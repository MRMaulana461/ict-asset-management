<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Asset;
use Carbon\Carbon;

class AssetSeeder extends Seeder
{
    public function run(): void
    {
        $assets = [
            [
                'asset_tag' => 'SAI-LAP-0123',
                'serial_number' => 'SN123456789',
                'asset_type' => 'Laptop',
                'current_owner' => 'Andi Budiman',
                'status' => 'Normal',
                'last_status_date' => Carbon::now()->subDays(30),
                'notes' => 'Dell Latitude 5420, RAM 16GB'
            ],
            [
                'asset_tag' => 'SAI-PC-0456',
                'serial_number' => 'SN987654321',
                'asset_type' => 'Desktop/PC',
                'current_owner' => 'Citra Lestari',
                'status' => 'Rusak',
                'last_status_date' => Carbon::now()->subDays(5),
                'notes' => 'Motherboard mati, perlu penggantian'
            ],
            [
                'asset_tag' => 'SAI-LAP-0789',
                'serial_number' => 'SN555666777',
                'asset_type' => 'Laptop',
                'current_owner' => 'Budi Santoso',
                'status' => 'Normal',
                'last_status_date' => Carbon::now()->subDays(15),
                'notes' => 'HP EliteBook 840 G8'
            ],
            [
                'asset_tag' => 'SAI-PRT-0111',
                'serial_number' => 'SN444333222',
                'asset_type' => 'Printer',
                'current_owner' => null,
                'status' => 'Normal',
                'last_status_date' => Carbon::now()->subDays(60),
                'notes' => 'HP LaserJet Pro M404dn'
            ],
            [
                'asset_tag' => 'SAI-MON-0222',
                'serial_number' => 'SN888999000',
                'asset_type' => 'Monitor',
                'current_owner' => 'Dewi Anggraini',
                'status' => 'Normal',
                'last_status_date' => Carbon::now()->subDays(10),
                'notes' => 'Dell 24" UltraSharp U2422H'
            ],
        ];

        foreach ($assets as $asset) {
            Asset::create($asset);
        }
    }
}