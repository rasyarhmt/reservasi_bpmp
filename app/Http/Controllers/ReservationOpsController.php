<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

class ReservationOpsController extends Controller
{
    public function __construct(private readonly CheckoutService $checkoutService) {}

    public function checkIn(Reservation $reservation): RedirectResponse
    {
        if ($reservation->status !== 'booked') {
            return back()->with('error', 'Reservasi ini tidak bisa check-in.');
        }

        if (Carbon::now()->toDateString() < $reservation->check_in_date->toDateString()) {
            return back()->with('error', 'Check-in belum bisa dilakukan. Tanggal check-in: '
                .$reservation->check_in_date->format('d M Y').'.');
        }

        $reservation->update(['status' => 'checked_in']);
        $this->updateRoomStatus($reservation, 'occupied');

        return back()->with('success', "Check-in {$reservation->booking_code} berhasil.");
    }

    public function checkOut(Reservation $reservation): RedirectResponse
    {
        if ($reservation->status !== 'checked_in') {
            return back()->with('error', 'Hanya tamu sedang inap yang bisa check-out.');
        }

        $log = $this->checkoutService->checkout($reservation, auth()->user());

        $message = $log->late_hours > 0
            ? "Check-out berhasil. Denda keterlambatan {$log->late_hours} jam: Rp".number_format($log->total_late_charge, 0, ',', '.').'.'
            : 'Check-out berhasil, tanpa denda keterlambatan.';

        return back()->with('success', $message);
    }

    private function updateRoomStatus(Reservation $reservation, string $status): void
    {
        $reservation->items()
            ->where('item_type', 'room')
            ->each(fn ($item) => $item->room?->update(['status' => $status]));
    }
}
