<?php

namespace Database\Seeders;

use App\Models\Hall;
use Illuminate\Database\Seeder;

class HallSeeder extends Seeder
{
    /**
     * Aula tetap — hanya satu, tidak dikelola lewat master data.
     */
    public function run(): void
    {
        Hall::updateOrCreate(
            ['hall_name' => 'Aula Banua To Dea'],
            [
                'capacity' => 70,
                'base_price' => 852479.76,
                'price_per_hour' => 106559.97,
                'status' => 'available',
                'cost_components' => Hall::DEFAULT_COST_COMPONENTS,
            ],
        );
    }
}
