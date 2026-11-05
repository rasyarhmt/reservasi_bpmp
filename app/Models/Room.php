<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'room_number',
    'room_name',
    'base_capacity',
    'base_price',
    'extra_person_rate',
    'status',
    'cost_components',
])]
class Room extends Model
{
    /**
     * Rincian biaya operasional tetap penyusun harga dasar per malam.
     */
    public const DEFAULT_COST_COMPONENTS = [
        'Listrik' => 33396.25,
        'Depresiasi' => 18035,
        'Gaji' => 160201,
        'Pemeliharaan' => 67662,
        'Pelayanan Kamar' => 80000,
    ];

    protected function casts(): array
    {
        return [
            'cost_components' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $room): void {
            $capacity = max(1, (int) $room->base_capacity);
            $room->extra_person_rate = round((float) $room->base_price / $capacity, 2);
        });
    }

    protected function extraPersonRate(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes): float {
                $capacity = max(1, (int) ($attributes['base_capacity'] ?? 1));
                $basePrice = (float) ($attributes['base_price'] ?? 0);

                return round($basePrice / $capacity, 2);
            },
        );
    }

    public function costComponents(): array
    {
        return $this->cost_components ?: self::DEFAULT_COST_COMPONENTS;
    }

    /**
     * Harga dasar per malam = total seluruh komponen biaya.
     */
    public function componentTotal(): float
    {
        return round(array_sum($this->costComponents()), 2);
    }

    public function amenities(): HasMany
    {
        return $this->hasMany(RoomAmenity::class);
    }

    public function reservations(): BelongsToMany
    {
        return $this->belongsToMany(Reservation::class, 'reservation_items', 'room_id', 'reservation_id');
    }

    /**
     * Kamar yang layak dipesan. Secara bawaan hanya menyaring kamar yang
     * sedang dalam perawatan; ketersediaan tanggal dicek terpisah lewat
     * scopeAvailableBetween() agar reservasi pada tanggal lain tidak
     * menyembunyikan kamar dari daftar.
     */
    public function scopeBookable(Builder $query): Builder
    {
        return $query->where('status', '!=', 'maintenance');
    }

    /**
     * Kamar yang tidak bentrok dengan reservasi aktif pada rentang tanggal
     * check-in/check-out yang diminta. Batas check-out tidak dihitung
     * sebagai bentrok karena kamar bisa dipesan lagi pada hari yang sama.
     */
    public function scopeAvailableBetween(Builder $query, string $checkIn, string $checkOut): Builder
    {
        return $query->whereDoesntHave('reservations', fn(Builder $reservation) => $reservation
            ->where('status', '!=', 'cancelled')
            ->where('check_in_date', '<', $checkOut)
            ->where('check_out_date', '>', $checkIn));
    }
}
