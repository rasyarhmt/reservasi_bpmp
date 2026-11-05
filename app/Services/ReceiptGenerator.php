<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\ReservationItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Geometry\Factories\CircleFactory;
use Intervention\Image\Geometry\Factories\RectangleFactory;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Typography\FontFactory;
use RuntimeException;

/**
 * Menggambar gambar resi reservasi sebagai PNG memakai Intervention Image
 * dengan driver GD (tanpa browser). Font memakai font sistem yang ada,
 * dapat dioverride lewat konfigurasi whatsapp.font_path.
 */
class ReceiptGenerator
{
    private const WIDTH = 760;

    private const MARGIN = 48;

    private const CONTENT_WIDTH = self::WIDTH - (self::MARGIN * 2);

    private const CANVAS_HEIGHT = 2600;

    private const HEADER_HEIGHT = 124;

    private const INK = '#0f172a';

    private const BODY = '#334155';

    private const MUTED = '#64748b';

    private const FAINT = '#94a3b8';

    private const LINE = '#e2e8f0';

    private const CARD = '#f1f5f9';

    private const WHITE = '#ffffff';

    private const SUCCESS = '#059669';

    private const DANGER = '#dc2626';

    private string $regularFont;

    private string $boldFont;

    private string $italicFont;

    /**
     * Buat gambar resi, simpan ke disk, dan kembalikan URL publik gambar
     * tersebut agar dapat diambil oleh layanan WhatsApp.
     */
    public function generate(Reservation $reservation): string
    {
        $reservation->loadMissing(['customer', 'items.room', 'items.hall']);

        $this->regularFont = $this->fontPath('normal');
        $this->boldFont = $this->fontPath('bold');
        $this->italicFont = $this->fontPath('italic');

        $manager = new ImageManager(new Driver);
        $image = $manager->createImage(self::WIDTH, self::CANVAS_HEIGHT)->fill(self::WHITE);

        $y = $this->drawHeader($image);
        $y = $this->drawBookingCard($image, $reservation, $y);
        $y = $this->drawCustomer($image, $reservation, $y);
        $y = $this->drawItems($image, $reservation, $y);
        $y = $this->drawTotal($image, $reservation, $y);
        $y = $this->drawPayment($image, $reservation, $y);
        $y = $this->drawFooter($image, $reservation, $y);

        $image = $image->crop(self::WIDTH, $y + self::MARGIN);

        $disk = (string) config('whatsapp.receipt_disk', 'public');
        $path = trim((string) config('whatsapp.receipt_path', 'receipts'), '/').'/'.$reservation->booking_code.'.png';

        $tmp = tempnam(sys_get_temp_dir(), 'resi').'.png';
        $image->save($tmp);
        Storage::disk($disk)->put($path, (string) file_get_contents($tmp));
        @unlink($tmp);

        return Storage::disk($disk)->url($path);
    }

    private function drawHeader(ImageInterface $image): int
    {
        $this->rect($image, 0, 0, self::WIDTH, self::HEADER_HEIGHT, self::INK);

        $appName = (string) config('app.name');
        $initial = mb_strtoupper(mb_substr($appName, 0, 1));

        $logoSize = 48;
        $logoY = 38;
        $this->roundedRect($image, self::MARGIN, $logoY, $logoSize, $logoSize, 12, self::WHITE);
        $this->text($image, $initial, self::MARGIN + (int) ($logoSize / 2), $logoY + (int) ($logoSize / 2), 24, self::INK, bold: true, align: 'center', valign: 'center');

        $textX = self::MARGIN + $logoSize + 18;
        $this->text($image, $appName, $textX, 42, 27, self::WHITE, bold: true);
        $this->text($image, 'BUKTI RESERVASI', $textX, 80, 13, self::FAINT);

        $this->text(
            $image,
            Carbon::now()->locale('id')->translatedFormat('d F Y'),
            self::WIDTH - self::MARGIN,
            48,
            14,
            self::FAINT,
            align: 'right',
        );

        return self::HEADER_HEIGHT;
    }

    private function drawBookingCard(ImageInterface $image, Reservation $reservation, int $top): int
    {
        $y = $top + 28;
        $cardHeight = 106;

        $this->roundedRect($image, self::MARGIN, $y, self::CONTENT_WIDTH, $cardHeight, 18, self::CARD);

        $innerX = self::MARGIN + 28;
        $this->text($image, 'KODE BOOKING', $innerX, $y + 26, 13, self::MUTED);
        $this->text($image, $reservation->booking_code, $innerX, $y + 48, 28, self::INK, bold: true);

        [$label, $color] = $this->statusMeta($reservation);
        $pillWidth = 158;
        $pillHeight = 40;
        $pillX = self::WIDTH - self::MARGIN - 28 - $pillWidth;
        $pillY = $y + (int) (($cardHeight - $pillHeight) / 2);

        $this->roundedRect($image, $pillX, $pillY, $pillWidth, $pillHeight, (int) ($pillHeight / 2), $color);
        $this->text($image, $label, $pillX + (int) ($pillWidth / 2), $pillY + (int) ($pillHeight / 2), 15, self::WHITE, bold: true, align: 'center', valign: 'center');

        return $y + $cardHeight;
    }

    private function drawCustomer(ImageInterface $image, Reservation $reservation, int $top): int
    {
        $y = $top + 40;

        $this->text($image, 'DIPESAN OLEH', self::MARGIN, $y, 13, self::MUTED);
        $y += 26;
        $this->text($image, $this->customerName($reservation), self::MARGIN, $y, 21, self::INK, bold: true);

        if ($phone = $this->displayPhone($reservation)) {
            $this->text($image, $phone, self::WIDTH - self::MARGIN, $y + 3, 16, self::BODY, align: 'right');
        }

        return $y + 21;
    }

    private function drawItems(ImageInterface $image, Reservation $reservation, int $top): int
    {
        $y = $top + 40;
        $y = $this->sectionTitle($image, 'DETAIL PESANAN', $y) + 4;

        foreach ($reservation->items as $item) {
            $details = $this->itemDetails($reservation, $item);
            $cardHeight = 34 + 42 + (count($details) * 30) + 34;

            $this->roundedRect($image, self::MARGIN, $y, self::CONTENT_WIDTH, $cardHeight, 16, self::CARD);

            $innerX = self::MARGIN + 26;
            $innerRight = self::WIDTH - self::MARGIN - 26;
            $rowY = $y + 26;

            $this->text($image, $this->itemTitle($item), $innerX, $rowY, 20, self::INK, bold: true);
            $rowY += 44;

            foreach ($details as $detail) {
                $this->text($image, $detail['label'], $innerX, $rowY, 15, self::MUTED, italic: $detail['italic'] ?? false);
                $this->text($image, $detail['value'], $innerRight, $rowY, 15, self::BODY, align: 'right');
                $rowY += 30;
            }

            $this->rect($image, $innerX, $rowY + 4, self::CONTENT_WIDTH - 52, 1, self::LINE);
            $rowY += 20;

            $this->text($image, 'Subtotal', $innerX, $rowY, 16, self::INK, bold: true);
            $this->text($image, 'Rp'.number_format((float) $item->subtotal, 0, ',', '.'), $innerRight, $rowY, 16, self::INK, bold: true, align: 'right');

            $y += $cardHeight + 16;
        }

        return $y - 16;
    }

    private function drawTotal(ImageInterface $image, Reservation $reservation, int $top): int
    {
        $y = $top + 24;
        $cardHeight = 78;

        $this->roundedRect($image, self::MARGIN, $y, self::CONTENT_WIDTH, $cardHeight, 16, self::INK);

        $midY = $y + (int) ($cardHeight / 2);
        $this->text($image, 'TOTAL PEMBAYARAN', self::MARGIN + 28, $midY, 14, self::FAINT, align: 'left', valign: 'center');
        $this->text($image, 'Rp'.number_format((float) $reservation->total_amount, 0, ',', '.'), self::WIDTH - self::MARGIN - 28, $midY, 28, self::WHITE, bold: true, align: 'right', valign: 'center');

        return $y + $cardHeight;
    }

    private function drawPayment(ImageInterface $image, Reservation $reservation, int $top): int
    {
        $y = $top + 30;

        $rows = [
            ['Pembayaran', $reservation->paymentSchemeLabel()],
            ['Status Reservasi', $reservation->statusLabel()],
        ];

        foreach ($rows as $row) {
            $this->text($image, $row[0], self::MARGIN, $y, 15, self::MUTED);
            $this->text($image, $row[1], self::WIDTH - self::MARGIN, $y, 15, self::INK, bold: true, align: 'right');
            $y += 32;
        }

        return $y;
    }

    private function drawFooter(ImageInterface $image, Reservation $reservation, int $top): int
    {
        $y = $top + 22;

        $this->rect($image, self::MARGIN, $y, self::CONTENT_WIDTH, 1, self::LINE);
        $y += 30;

        $this->text($image, 'Terima kasih telah memesan di '.config('app.name').'.', (int) (self::WIDTH / 2), $y, 15, self::MUTED, align: 'center');
        $y += 26;
        $this->text($image, 'Simpan bukti ini sebagai referensi Anda.', (int) (self::WIDTH / 2), $y, 13, self::FAINT, align: 'center');

        return $y + 24;
    }

    private function sectionTitle(ImageInterface $image, string $title, int $y): int
    {
        $this->rect($image, self::MARGIN, $y + 3, 4, 18, self::INK);
        $this->text($image, $title, self::MARGIN + 16, $y, 15, self::INK, bold: true);

        return $y + 34;
    }

    private function itemTitle(ReservationItem $item): string
    {
        if ($item->item_type === 'hall') {
            return 'Aula '.($item->hall?->hall_name ?? '-');
        }

        return 'Kamar '.($item->room?->room_number ?? '-');
    }

    /**
     * @return array<int, array{label: string, value: string, italic?: bool}>
     */
    private function itemDetails(Reservation $reservation, ReservationItem $item): array
    {
        if ($item->item_type === 'hall') {
            $start = $item->hall_start_time;
            $end = $item->hall_end_time;
            $hours = $item->hall_hours
                ?? ($start && $end ? (int) round($start->diffInSeconds($end) / 3600) : 0);

            return [
                ['label' => 'Tanggal', 'value' => $this->formatDate($start)],
                ['label' => 'Waktu', 'value' => $this->formatTime($start).' - '.$this->formatTime($end)],
                ['label' => 'Durasi', 'value' => $hours.' jam'],
            ];
        }

        $checkIn = $reservation->check_in_date;
        $checkOut = $reservation->check_out_date;
        $nights = $checkIn && $checkOut ? (int) $checkIn->diffInDays($checkOut) : 0;

        return [
            ['label' => 'Check-in', 'value' => $this->formatDate($checkIn), 'italic' => true],
            ['label' => 'Check-out', 'value' => $this->formatDate($checkOut), 'italic' => true],
            ['label' => 'Durasi', 'value' => $nights.' malam'],
            ['label' => 'Jumlah tamu', 'value' => $item->guest_count.' orang'],
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function statusMeta(Reservation $reservation): array
    {
        $color = match ($reservation->status) {
            'checked_in' => self::SUCCESS,
            'cancelled' => self::DANGER,
            default => self::INK,
        };

        return [$reservation->statusLabel(), $color];
    }

    private function customerName(Reservation $reservation): string
    {
        return $reservation->customer_name
            ?: ($reservation->customer?->name ?? 'Pelanggan');
    }

    private function displayPhone(Reservation $reservation): ?string
    {
        return $reservation->customer_phone ?: $reservation->customer?->phone;
    }

    private function formatDate(?Carbon $date): string
    {
        return $date?->locale('id')->translatedFormat('d F Y') ?? '-';
    }

    private function formatTime(?Carbon $date): string
    {
        return $date?->format('H:i') ?? '-';
    }

    private function text(
        ImageInterface $image,
        string $text,
        int $x,
        int $y,
        int $size,
        string $color,
        bool $bold = false,
        string $align = 'left',
        string $valign = 'top',
        bool $italic = false,
    ): void {
        if ($text === '') {
            return;
        }

        $font = match (true) {
            $bold => $this->boldFont,
            $italic => $this->italicFont,
            default => $this->regularFont,
        };

        $image->text($text, $x, $y, function (FontFactory $factory) use ($font, $size, $color, $align, $valign): void {
            $factory->filename($font)
                ->size($size)
                ->color($color)
                ->align($align, $valign);
        });
    }

    private function rect(ImageInterface $image, int $x, int $y, int $width, int $height, string $color): void
    {
        if ($width <= 0 || $height <= 0) {
            return;
        }

        $image->drawRectangle(fn (RectangleFactory $rect) => $rect
            ->size($width, $height)
            ->background($color)
            ->at($x, $y));
    }

    private function roundedRect(ImageInterface $image, int $x, int $y, int $width, int $height, int $radius, string $color): void
    {
        $radius = min($radius, (int) ($height / 2), (int) ($width / 2));

        $this->rect($image, $x + $radius, $y, $width - ($radius * 2), $height, $color);
        $this->rect($image, $x, $y + $radius, $width, $height - ($radius * 2), $color);

        if ($radius <= 0) {
            return;
        }

        $corners = [
            [$x + $radius, $y + $radius],
            [$x + $width - $radius, $y + $radius],
            [$x + $radius, $y + $height - $radius],
            [$x + $width - $radius, $y + $height - $radius],
        ];

        foreach ($corners as [$cx, $cy]) {
            $image->drawCircle(fn (CircleFactory $circle) => $circle
                ->diameter($radius * 2)
                ->background($color)
                ->at($cx, $cy));
        }
    }

    private function fontPath(string $style): string
    {
        $configured = match ($style) {
            'bold' => config('whatsapp.font_bold_path'),
            'italic' => config('whatsapp.font_italic_path'),
            default => config('whatsapp.font_path'),
        };

        if (is_string($configured) && $configured !== '' && is_file($configured)) {
            return $configured;
        }

        foreach ($this->fontCandidates($style) as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        if ($style !== 'normal') {
            return $this->fontPath('normal');
        }

        foreach (['/usr/share/fonts/*/*.ttf', '/usr/share/fonts/*/*/*.ttf'] as $pattern) {
            foreach (glob($pattern) ?: [] as $found) {
                if (is_file($found)) {
                    return $found;
                }
            }
        }

        $variable = $style === 'bold' ? 'WA_FONT_BOLD_PATH' : 'WA_FONT_PATH';

        throw new RuntimeException(
            "Font untuk gambar resi tidak ditemukan. Set {$variable} ke lokasi file .ttf."
        );
    }

    /**
     * @return array<int, string>
     */
    private function fontCandidates(string $style): array
    {
        return match ($style) {
            'bold' => [
                '/usr/share/fonts/dejavu-sans-fonts/DejaVuSans-Bold.ttf',
                '/usr/share/fonts/liberation-sans-fonts/LiberationSans-Bold.ttf',
                '/usr/share/fonts/google-carlito-fonts/Carlito-Bold.ttf',
                '/usr/share/fonts/adwaita-sans-fonts/AdwaitaSans-Bold.ttf',
                '/Library/Fonts/Arial Bold.ttf',
                '/System/Library/Fonts/Supplemental/Arial Bold.ttf',
            ],
            'italic' => [
                '/usr/share/fonts/dejavu-sans-fonts/DejaVuSans-Oblique.ttf',
                '/usr/share/fonts/liberation-sans-fonts/LiberationSans-Italic.ttf',
                '/usr/share/fonts/google-carlito-fonts/Carlito-Italic.ttf',
                '/Library/Fonts/Arial Italic.ttf',
                '/System/Library/Fonts/Supplemental/Arial Italic.ttf',
            ],
            default => [
                '/usr/share/fonts/dejavu-sans-fonts/DejaVuSans.ttf',
                '/usr/share/fonts/liberation-sans-fonts/LiberationSans-Regular.ttf',
                '/usr/share/fonts/google-carlito-fonts/Carlito-Regular.ttf',
                '/usr/share/fonts/adwaita-sans-fonts/AdwaitaSans-Regular.ttf',
                '/Library/Fonts/Arial.ttf',
                '/System/Library/Fonts/Supplemental/Arial.ttf',
            ],
        };
    }
}
