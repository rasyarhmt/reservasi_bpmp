<?php

namespace App\Jobs;

use App\Models\Reservation;
use App\Models\ReservationItem;
use App\Services\ReceiptGenerator;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Kirim pesan WhatsApp berisi ringkasan reservasi beserta gambar resi ke
 * nomor pelanggan. Dijalankan lewat queue agar tidak memperlambat request.
 */
class SendReservationReceipt implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(public Reservation $reservation) {}

    public function handle(WhatsAppService $whatsApp, ReceiptGenerator $receipts): void
    {
        if (! $whatsApp->isEnabled()) {
            return;
        }

        $reservation = $this->reservation->fresh(['customer', 'items.room', 'items.hall']);

        if ($reservation === null) {
            return;
        }

        $phone = $this->phone($reservation);

        if ($phone === null) {
            Log::info('Reservasi tanpa nomor WhatsApp, notifikasi dilewati.', [
                'booking_code' => $reservation->booking_code,
            ]);

            return;
        }

        try {
            $receiptUrl = $receipts->generate($reservation);
        } catch (Throwable $e) {
            Log::error('Gagal membuat gambar resi.', [
                'booking_code' => $reservation->booking_code,
                'error' => $e->getMessage(),
            ]);

            $receiptUrl = null;
        }

        $result = $whatsApp->send($phone, $this->buildMessage($reservation), $receiptUrl);

        if (! $result['ok']) {
            throw new RuntimeException('Gagal mengirim WhatsApp: '.$result['error']);
        }
    }

    public function buildMessage(Reservation $reservation): string
    {
        $name = $reservation->customer_name ?: ($reservation->customer?->name ?? 'Pelanggan');

        $lines = [
            'Hai, '.$name.'!',
            '',
            'Pesanan '.$this->itemType($reservation).' Anda untuk '.$this->durationText($reservation).' telah direservasi.',
            '',
            'Kode Booking: '.$reservation->booking_code,
        ];

        foreach ($reservation->items as $item) {
            $lines[] = $this->itemSummary($reservation, $item);
        }

        $lines[] = 'Total: Rp'.number_format((float) $reservation->total_amount, 0, ',', '.');
        $lines[] = 'Pembayaran: '.$reservation->paymentSchemeLabel();
        $lines[] = '';
        $lines[] = 'Bukti reservasi kami lampirkan pada gambar berikut.';
        $lines[] = 'Terima kasih telah memesan di '.config('app.name').'.';

        return implode("\n", $lines);
    }

    private function phone(Reservation $reservation): ?string
    {
        return $reservation->customer_phone ?: $reservation->customer?->phone;
    }

    private function itemType(Reservation $reservation): string
    {
        $item = $reservation->items->first();

        return $item?->item_type === 'hall' ? 'aula' : 'kamar';
    }

    private function itemSummary(Reservation $reservation, ReservationItem $item): string
    {
        if ($item->item_type === 'hall') {
            $start = $item->hall_start_time;
            $end = $item->hall_end_time;
            $hours = (int) ($item->hall_hours
                ?? ($start && $end ? round($start->diffInSeconds($end) / 3600) : 0));

            return sprintf(
                '- Aula %s: %s, %s-%s (%d jam)',
                $item->hall?->hall_name ?? '-',
                $this->formatDate($start),
                $this->formatTime($start),
                $this->formatTime($end),
                $hours,
            );
        }

        $nights = $reservation->check_in_date && $reservation->check_out_date
            ? (int) $reservation->check_in_date->diffInDays($reservation->check_out_date)
            : 0;

        return sprintf(
            '- Kamar %s: %s s/d %s (%d malam, %d tamu)',
            $item->room?->room_number ?? '-',
            $this->formatDate($reservation->check_in_date),
            $this->formatDate($reservation->check_out_date),
            $nights,
            (int) $item->guest_count,
        );
    }

    private function durationText(Reservation $reservation): string
    {
        $item = $reservation->items->first();

        if ($item?->item_type === 'hall') {
            return (int) ($item->hall_hours ?? 0).' jam';
        }

        $nights = $reservation->check_in_date && $reservation->check_out_date
            ? (int) $reservation->check_in_date->diffInDays($reservation->check_out_date)
            : 0;

        return $nights.' malam';
    }

    private function formatDate(?Carbon $date): string
    {
        return $date?->locale('id')->translatedFormat('d F Y') ?? '-';
    }

    private function formatTime(?Carbon $date): string
    {
        return $date?->format('H:i') ?? '-';
    }
}
