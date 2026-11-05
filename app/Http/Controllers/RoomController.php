<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomController extends Controller
{
    /**
     * Detail internal kamar (komponen perhitungan & fasilitas) — hanya untuk admin.
     */
    public function show(Room $room): View
    {
        return view('admin.rooms.show', [
            'room' => $room->load('amenities'),
        ]);
    }

    /**
     * Form edit kamar (fasilitas, status, harga, dll) — hanya untuk admin.
     */
    public function edit(Room $room): View
    {
        return view('admin.rooms.form', [
            'room' => $room->load('amenities'),
        ]);
    }

    public function update(Request $request, Room $room): RedirectResponse
    {
        $data = $request->validate([
            'room_number' => ['required', 'string', 'max:20', 'unique:rooms,room_number,' . $room->id],
            'room_name' => ['required', 'string', 'max:100'],
            'base_capacity' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:available,occupied,maintenance'],
            'cost_components' => ['required', 'array', 'min:1'],
            'cost_components.*.label' => ['required', 'string', 'max:100'],
            'cost_components.*.amount' => ['required', 'numeric', 'min:0'],
            'amenities' => ['nullable', 'string'],
        ]);

        // Kalkulasi perhitungan komponen biaya
        $components = collect($data['cost_components'])
            ->mapWithKeys(fn(array $component) => [
                trim($component['label']) => round((float) $component['amount'], 2),
            ])
            ->all();

        $basePrice = round(array_sum($components), 2);

        // 1. Pisahkan data yang akan diterapkan ke SEMUA KAMAR (Template)
        // Hapus 'room_name' dari sini agar tidak menimpa nama kamar lain
        $sharedData = [
            'base_capacity' => $data['base_capacity'],
            'cost_components' => $components,
            'base_price' => $basePrice,
            'extra_person_rate' => round($basePrice / max(1, (int) $data['base_capacity']), 2),
        ];

        $amenities = $data['amenities'] ?? null;

        // 2. Ambil seluruh kamar
        $allRooms = Room::all();

        // 3. Loop dan update semua kamar
        foreach ($allRooms as $currentRoom) {
            if ($currentRoom->id === $room->id) {
                // Khusus untuk kamar yang sedang di-edit di form, update juga field uniknya (termasuk room_name)
                $currentRoom->update(array_merge($sharedData, [
                    'room_number' => $data['room_number'],
                    'room_name' => $data['room_name'],
                    'status' => $data['status'],
                ]));
            } else {
                // Untuk sisa 24 kamar lainnya, HANYA update komponen biaya dan kapasitas
                // (Jangan menimpa room_number, room_name, dan status kamar lain)
                $currentRoom->update($sharedData);
            }

            // Sync fasilitas untuk setiap kamar agar semuanya seragam
            $this->syncAmenities($currentRoom, $amenities);
        }

        return redirect()
            ->route('rooms.show', $room)
            ->with('success', "Kamar {$room->room_number} dan komponen harga seluruh kamar lainnya berhasil diperbarui.");
    }

    private function syncAmenities(Room $room, ?string $text): void
    {
        $names = preg_split('/\R+/', trim($text ?? '')) ?: [];
        $names = collect($names)
            ->map(fn(string $name) => trim($name))
            ->filter()
            ->unique()
            ->values();

        $room->amenities()->delete();
        $names->each(fn(string $name) => $room->amenities()->create(['amenity_name' => $name]));
    }
}
