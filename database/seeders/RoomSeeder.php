<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Kamar tetap — 25 kamar (101–125), kapasitas dasar 4 orang, semua tersedia.
     */
    public function run(): void
    {
        $facilities = [
            'Kasur/Spring Bed',
            'Nakas',
            'Backdrop TV/Wardrobe',
            'Televisi',
            'Lampu Belajar',
            'Air Conditioning (AC)',
        ];

        foreach (range(101, 125) as $number) {
            $baseCapacity = 4;
            $basePrice = 359294.25;

            $room = Room::firstOrCreate(
                ['room_number' => (string) $number],
                [
                    'room_name' => 'Kamar ' . $number,
                    'base_capacity' => $baseCapacity,
                    'base_price' => $basePrice,
                    'extra_person_rate' => round($basePrice / $baseCapacity, 2),
                    'status' => 'available',
                    'cost_components' => Room::DEFAULT_COST_COMPONENTS,
                ],
            );

            foreach ($facilities as $facility) {
                $room->amenities()->firstOrCreate(['amenity_name' => $facility]);
            }
        }
    }
}
