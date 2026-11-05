<?php

namespace App\Services;

use App\Models\CheckoutLog;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Carbon;

class CheckoutService
{
    /**
     * Proses check-out: hitung denda keterlambatan, buat log, dan tutup reservasi.
     */
    public function checkout(Reservation $reservation, User $staff): CheckoutLog
    {
        $actualTime = now();
        $lateHours = $this->calculateLateHours($reservation, $actualTime);
        $lateCharge = $this->calculateLateCharge($reservation, $lateHours);

        $unpaidBase = max(0, (float) $reservation->total_amount - (float) $reservation->total_paid);
        $finalSettlement = $unpaidBase + $lateCharge;

        $log = $reservation->checkoutLog()->create([
            'processed_by' => $staff->id,
            'actual_checkout_time' => $actualTime,
            'late_hours' => $lateHours,
            'hourly_charge_rate' => $this->referenceHourlyRate($reservation),
            'total_late_charge' => $lateCharge,
            'unpaid_base_amount' => $unpaidBase,
            'final_settlement_amount' => $finalSettlement,
            'is_settled' => $finalSettlement <= 0,
        ]);

        $reservation->update(['status' => 'checked_out']);
        $this->updateRoomStatus($reservation, 'available');

        return $log;
    }

    /**
     * Jam keterlambatan = ceil kelebihan waktu setelah batas 12:00 + toleransi 15 menit.
     */
    public function calculateLateHours(Reservation $reservation, Carbon $actualTime): int
    {
        $scheduled = Carbon::parse($reservation->check_out_date)
            ->setTimeFromTimeString($reservation->scheduled_checkout_time);
        $graceEnd = $scheduled->copy()->addMinutes(CheckoutLog::GRACE_MINUTES);

        if (!$actualTime->greaterThan($graceEnd)) {
            return 0;
        }

        return (int) ceil($graceEnd->diffInSeconds($actualTime) / 3600);
    }

    /**
     * Denda per item: kamar di-charge per jam (base_price ÷ 21 jam layanan),
     * aula di-charge per jam sesuai rate sewanya. Tidak saling bergantung.
     */
    public function calculateLateCharge(Reservation $reservation, int $lateHours): float
    {
        return $reservation->items->reduce(function (float $carry, $item) use ($lateHours): float {
            $rate = $item->item_type === 'room'
                ? round((float) $item->unit_price_snapshot / CheckoutLog::ROOM_DAY_HOURS)
                : (float) $item->unit_price_snapshot;

            return $carry + $lateHours * $rate;
        }, 0.0);
    }

    private function referenceHourlyRate(Reservation $reservation): float
    {
        foreach ($reservation->items as $item) {
            return $item->item_type === 'room'
                ? round((float) $item->unit_price_snapshot / CheckoutLog::ROOM_DAY_HOURS)
                : (float) $item->unit_price_snapshot;
        }

        return 0.0;
    }

    private function updateRoomStatus(Reservation $reservation, string $status): void
    {
        $reservation->items()
            ->where('item_type', 'room')
            ->each(fn($item) => $item->room?->update(['status' => $status]));
    }
}
