<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    /**
     * Data master untuk pengujian: kamar, fasilitas, dan aula.
     */
    public function run(): void
    {
        $this->call([
            RoomSeeder::class,
            HallSeeder::class,
        ]);
    }
}
