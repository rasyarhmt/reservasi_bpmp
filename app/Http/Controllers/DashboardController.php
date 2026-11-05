<?php

namespace App\Http\Controllers;

use App\Models\PaymentTransaction;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        if ($request->user()->isAdmin()) {

            $paidReservations = Reservation::with(['checkoutLog', 'items.room', 'items.hall'])
                ->where('total_paid', '>', 0)
                ->get();

            $dpRevenue = 0;
            $doneRevenue = 0;

            foreach ($paidReservations as $reservation) {
                if ($reservation->remainingAmount() > 0) {
                    $dpRevenue += (float) $reservation->total_paid;
                } else {
                    $doneRevenue += (float) $reservation->total_paid;
                }
            }

            $schedules = [
                'Senin' => [
                    '07.00 - 15.00' => ['Rosmawati', 'Litserman'],
                    '15.00 - 23.00' => ['Berlin'],
                    '23.00 - 07.00' => ['Ridwan'],
                ],
                'Selasa' => [
                    '07.00 - 15.00' => ['Rosmawati', 'Litserman'],
                    '15.00 - 23.00' => ['Syafaat'],
                    '23.00 - 07.00' => ['Berlin'],
                ],
                'Rabu' => [
                    '07.00 - 15.00' => ['Hardiantoro', 'Syafaat'],
                    '15.00 - 23.00' => ['Rosmawati'],
                    '23.00 - 07.00' => ['Litserman'],
                ],
                'Kamis' => [
                    '07.00 - 15.00' => ['Ridwan', 'Hardiantoro'],
                    '15.00 - 23.00' => ['Syafaat'],
                    '23.00 - 07.00' => ['Rosmawati'],
                ],
                'Jumat' => [
                    '07.00 - 15.00' => ['Berlin', 'Ridwan'],
                    '15.00 - 23.00' => ['Hardiantoro'],
                    '23.00 - 07.00' => ['Syafaat'],
                ],
                'Sabtu' => [
                    '07.00 - 15.00' => ['Litserman', 'Berlin'],
                    '15.00 - 23.00' => ['Ridwan'],
                    '23.00 - 07.00' => ['Hardiantoro', 'Syafaat'],
                ],
                'Minggu' => [
                    '07.00 - 15.00' => ['Rosmawati', 'Hardiantoro'],
                    '15.00 - 23.00' => ['Litserman'],
                    '23.00 - 07.00' => ['Berlin', 'Ridwan'],
                ],
            ];

            $currentDay = now()->locale('id')->isoFormat('dddd');
            $todaySchedule = $schedules[$currentDay] ?? [];

            return view('dashboard.admin', [
                'stats' => [
                    'totalReservations' => Reservation::count(),
                    'booked' => Reservation::where('status', 'booked')->count(),
                    'checkedIn' => Reservation::where('status', 'checked_in')->count(),
                    'revenue' => (float) PaymentTransaction::sum('amount'),
                    'dpRevenue' => $dpRevenue,
                    'doneRevenue' => $doneRevenue,
                ],
                'activeReservations' => Reservation::with(['customer', 'items.room', 'items.hall'])
                    ->whereIn('status', ['booked', 'checked_in'])
                    ->orderBy('check_in_date')
                    ->limit(3)
                    ->get(),
                'unsettledReservations' => Reservation::with(['customer', 'checkoutLog'])
                    ->where('status', 'checked_out')
                    ->whereRelation('checkoutLog', 'is_settled', false)
                    ->latest()
                    ->limit(3)
                    ->get(),
                // Kirim data hari dan jadwal ke view
                'currentDay' => $currentDay,
                'todaySchedule' => $todaySchedule,
            ]);
        }

        return redirect()->route('home');
    }
}