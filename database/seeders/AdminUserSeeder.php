<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin ICT',
            'email' => 'admin@saipem.local',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'ICT Karimun',
            'email' => 'ict@saipem.com',
            'password' => Hash::make('ict2025'),
            'email_verified_at' => now(),
        ]);
    }
}