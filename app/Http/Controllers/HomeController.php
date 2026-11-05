<?php

namespace App\Http\Controllers;

use App\Models\Hall;
use App\Models\Room;
use Illuminate\Support\Js;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Halaman depan (landing page) penginapan.
     *
     * Data harga dihitung dari master data kamar & aula agar pratinjau
     * kalkulator di halaman depan selalu sinkron dengan harga sebenarnya.
     */
    public function index(): View
    {
        $rooms = Room::bookable()
            ->orderBy('room_number')
            ->get(['id', 'room_number', 'room_name', 'base_capacity', 'base_price', 'extra_person_rate']);

        if ($rooms->isEmpty()) {
            // Seluruh kamar sedang terbooking: tetap tampilkan exemplary agar
            // kalkulator pratinjau tidak kosong.
            $rooms = Room::orderBy('room_number')
                ->limit(6)
                ->get(['id', 'room_number', 'room_name', 'base_capacity', 'base_price', 'extra_person_rate']);
        }

        $halls = Hall::where('status', '!=', 'maintenance')
            ->orderBy('hall_name')
            ->get(['id', 'hall_name', 'capacity', 'base_price', 'price_per_hour']);

        $featuredRoom = $rooms->first();
        $featuredHall = $halls->first();

        return view('welcome', [
            'rooms' => $rooms,
            'halls' => $halls,
            'featuredRoom' => $featuredRoom,
            'featuredHall' => $featuredHall,
            'roomTotal' => Room::count(),
            'hallCapacity' => (int) $halls->sum('capacity'),
            'preview' => $this->preview($featuredRoom, $featuredHall),
            'calculator' => Js::from([
                'rooms' => $rooms->map(fn (Room $room) => [
                    'id' => $room->id,
                    'label' => $room->room_name,
                    'base_capacity' => (int) $room->base_capacity,
                    'base_price' => (float) $room->base_price,
                    'extra_person_rate' => (float) $room->extra_person_rate,
                ])->values(),
                'halls' => $halls->map(fn (Hall $hall) => [
                    'id' => $hall->id,
                    'label' => $hall->hall_name,
                    'capacity' => (int) $hall->capacity,
                    'daily_rate' => (float) $hall->dailyRate(),
                ])->values(),
            ]),
        ]);
    }

    /**
     * Nilai awal kalkulator yang sudah dirender di server.
     *
     * Dipakai sebagai isi fallback pada elemen x-text, sehingga angkanya tetap
     * terbaca sebelum Alpine aktif (atau ketika JavaScript gagal dimuat).
     */
    private function preview(?Room $room, ?Hall $hall): array
    {
        $basePrice = (float) ($room?->base_price ?? 0);
        $extraRate = (float) ($room?->extra_person_rate ?? 0);
        $hallPrice = (float) ($hall?->dailyRate() ?? 0);

        return [
            'nights' => 1,
            'guests' => (int) ($room?->base_capacity ?? 1),
            'capacity' => (int) ($hall?->capacity ?? 0),
            'baseTotal' => $this->rupiah($basePrice),
            'extraTotal' => $this->rupiah(0),
            'extraPersonRate' => $this->rupiah($extraRate),
            'hallTotal' => $this->rupiah($hallPrice),
            'roomTotal' => $this->rupiah($basePrice),
        ];
    }

    private function rupiah(float $amount): string
    {
        return 'Rp'.number_format(round($amount), 0, ',', '.');
    }
}
