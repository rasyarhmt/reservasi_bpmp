<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['hall_name', 'capacity', 'base_price', 'price_per_hour', 'status', 'cost_components'])]
class Hall extends Model
{
    /**
     * Jam mulai operasional sewa aula.
     */
    public const OPERATIONAL_START = '08:00';

    /**
     * Jam selesai operasional sewa aula.
     */
    public const OPERATIONAL_END = '16:00';

    /**
     * Lama operasional sewa aula per hari (jam).
     */
    public const OPERATIONAL_HOURS = 8;

    /**
     * Rincian biaya operasional tetap penyusun harga dasar aula per hari.
     * Mengikuti gaya komponen kamar: nominal bulat tanpa baris pembulatan.
     */
    public const DEFAULT_COST_COMPONENTS = [
        'Listrik' => 190992.76,
        'Penyusutan aset' => 353624,
        'Alokasi gaji' => 160201,
        'Luas gedung pemeliharaan' => 67662,
        'Biaya perlengkapan kebersihan' => 80000,
    ];

    public function costComponents(): array
    {
        return $this->cost_components ?: self::DEFAULT_COST_COMPONENTS;
    }

    /**
     * Harga dasar per hari = total seluruh komponen biaya.
     */
    public function componentTotal(): float
    {
        return round(array_sum($this->costComponents()), 2);
    }

    /**
     * Tarif sewa per tanggal (satu hari operasional 08:00-16:00).
     */
    public function dailyRate(): float
    {
        return round((float) $this->base_price, 2);
    }

    /**
     * Tarif denda keterlambatan per jam. Diturunkan dari harga dasar dibagi
     * lama operasional per hari agar satu hari penuh sama dengan harga dasar.
     */
    public function lateChargeRate(): float
    {
        return round($this->dailyRate() / self::OPERATIONAL_HOURS, 2);
    }

    public function getOperationalStartAttribute(): string
    {
        return self::OPERATIONAL_START;
    }

    public function getOperationalEndAttribute(): string
    {
        return self::OPERATIONAL_END;
    }

    public function getOperationalHoursAttribute(): int
    {
        return self::OPERATIONAL_HOURS;
    }

    protected function casts(): array
    {
        return [
            'cost_components' => 'array',
        ];
    }
}
