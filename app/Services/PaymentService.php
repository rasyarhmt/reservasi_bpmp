<?php

namespace App\Services;

use App\Models\PaymentTransaction;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    /**
     * Catat pembayaran manual dan perbarui total yang sudah dibayar.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function record(Reservation $reservation, array $data, ?User $staff): PaymentTransaction
    {
        $amount = (float) $data['amount'];
        $remaining = $reservation->remainingAmount();

        // Sisa tagihan bisa pecahan sen, jadi nominal dibulatkan ke atas
        // supaya pelunasan bisa dibayar penuh dalam satu kali transaksi.
        $maxAmount = (float) ceil($remaining);

        if ($amount <= 0 || $amount > $maxAmount) {
            throw ValidationException::withMessages([
                'amount' => 'Nominal harus di antara Rp1 dan sisa tagihan Rp'.number_format($maxAmount, 0, ',', '.').'.',
            ]);
        }

        $transaction = $reservation->payments()->create([
            'checkout_log_id' => $reservation->checkoutLog?->id,
            'received_by' => $staff?->id,
            'payment_stage' => $this->deriveStage($reservation, $amount),
            'amount' => $amount,
            'payment_method' => $data['payment_method'],
            'payment_proof' => $data['payment_proof'] ?? null,
            'transaction_date' => now(),
            'notes' => $data['notes'] ?? null,
        ]);

        $reservation->update([
            'total_paid' => min(
                (float) $reservation->finalTotal(),
                (float) $reservation->total_paid + $amount
            ),
        ]);

        if ($reservation->checkoutLog && $reservation->remainingAmount() <= 0) {
            $reservation->checkoutLog->update(['is_settled' => true]);
        }

        return $transaction;
    }

    private function deriveStage(Reservation $reservation, float $amount): string
    {
        if ($reservation->status === 'checked_out') {
            return $this->baseRemaining($reservation) > 0 ? 'settlement' : 'late_charge_settlement';
        }

        if ($reservation->status === 'checked_in') {
            return 'settlement';
        }

        if ($reservation->payments()->exists()) {
            return 'settlement';
        }

        $remainingAfter = (float) $reservation->total_amount - ((float) $reservation->total_paid + $amount);

        return $remainingAfter > 0 ? 'down_payment' : 'settlement';
    }

    private function baseRemaining(Reservation $reservation): float
    {
        return max(0, (float) $reservation->total_amount - (float) $reservation->total_paid);
    }
}
