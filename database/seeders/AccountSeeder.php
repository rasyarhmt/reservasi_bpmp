<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admincalculia@gmail.com'],
            ['name' => 'Admin Resepsionis', 'role' => User::ROLE_ADMIN, 'password' => 'password'],
        );
    }
}
