<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Klien HTTP ke layanan WhatsApp (Baileys) yang berjalan terpisah.
 */
class WhatsAppService
{
    public function isEnabled(): bool
    {
        return (bool) config('whatsapp.enabled');
    }

    /**
     * Normalisasi nomor telepon Indonesia menjadi format internasional tanpa
     * tanda "+", mis. "0812-3456-7890" menjadi "6281234567890".
     */
    public function normalizePhone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return null;
        }

        $countryCode = (string) config('whatsapp.country_code', '62');

        if (str_starts_with($digits, '0')) {
            $digits = $countryCode.substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = $countryCode.$digits;
        }

        return $digits;
    }

    /**
     * Kirim pesan teks (opsional dengan gambar via URL) ke nomor tujuan.
     *
     * Layanan WhatsApp (wa-service) akan mengambil gambar dari $imageUrl
     * lalu mengirimkannya sebagai caption pesan.
     *
     * @return array{ok: bool, error: ?string}
     */
    public function send(?string $phone, string $message, ?string $imageUrl = null): array
    {
        $target = $this->normalizePhone($phone);

        if ($target === null) {
            return ['ok' => false, 'error' => 'Nomor WhatsApp tidak tersedia.'];
        }

        $payload = [
            'to' => $target,
            'message' => $message,
        ];

        if ($imageUrl !== null && $imageUrl !== '') {
            $payload['image_url'] = $imageUrl;
        }

        try {
            $response = Http::withToken((string) config('whatsapp.token'))
                ->acceptJson()
                ->timeout((int) config('whatsapp.timeout', 15))
                ->post(rtrim((string) config('whatsapp.service_url'), '/').'/send', $payload);
        } catch (ConnectionException $e) {
            Log::warning('Layanan WhatsApp tidak dapat dihubungi.', [
                'phone' => $target,
                'error' => $e->getMessage(),
            ]);

            return ['ok' => false, 'error' => 'Layanan WhatsApp tidak dapat dihubungi.'];
        }

        if (! $response->successful() || $response->json('success') === false) {
            return [
                'ok' => false,
                'error' => $response->json('message')
                    ?? 'Layanan WhatsApp menolak permintaan (HTTP '.$response->status().').',
            ];
        }

        return ['ok' => true, 'error' => null];
    }
}
