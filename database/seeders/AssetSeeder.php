<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Asset;
use App\Models\AssetType;
use App\Models\Employee;
use Carbon\Carbon;

class AssetSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan ada employees dulu
        $employee1 = Employee::firstOrCreate(
            ['employee_id' => 'SAI001'],
            [
                'user_id' => 'USR001',
                'name' => 'Andi Budiman',
                'email' => 'andi.budiman@saipem.com',
                'department' => 'IT Department',
                'cost_center' => '862321',
                'is_active' => true
            ]
        );

        $employee2 = Employee::firstOrCreate(
            ['employee_id' => 'SAI002'],
            [
                'user_id' => 'USR002',
                'name' => 'Citra Lestari',
                'email' => 'citra.lestari@saipem.com',
                'department' => 'Finance',
                'cost_center' => '862316',
                'is_active' => true
            ]
        );

        // Get asset types
        $laptop = AssetType::where('name', 'Laptop')->first();
        $desktop = AssetType::where('name', 'Desktop/PC')->first();
        $printer = AssetType::where('name', 'Printer')->first();
        $monitor = AssetType::where('name', 'Monitor')->first();

        $assets = [
            [
                'asset_tag' => 'SAI-LAP-0123',
                'serial_number' => 'SN123456789',
                'asset_type_id' => $laptop->id,
                'assigned_to' => $employee1->id,
                'assignment_date' => Carbon::now()->subDays(30),
                'status' => 'In Use',
                'last_status_date' => Carbon::now()->subDays(30),
                'notes' => 'Dell Latitude 5420, RAM 16GB'
            ],
            [
                'asset_tag' => 'SAI-PC-0456',
                'serial_number' => 'SN987654321',
                'asset_type_id' => $desktop->id,
                'assigned_to' => $employee2->id,
                'assignment_date' => Carbon::now()->subDays(10),
                'status' => 'Broken',
                'last_status_date' => Carbon::now()->subDays(5),
                'notes' => 'Motherboard mati, perlu penggantian'
            ],
            [
                'asset_tag' => 'SAI-LAP-0789',
                'serial_number' => 'SN555666777',
                'asset_type_id' => $laptop->id,
                'assigned_to' => null,
                'assignment_date' => null,
                'status' => 'In Stock',
                'last_status_date' => Carbon::now()->subDays(15),
                'notes' => 'HP EliteBook 840 G8'
            ],
            [
                'asset_tag' => 'SAI-PRT-0111',
                'serial_number' => 'SN444333222',
                'asset_type_id' => $printer->id,
                'assigned_to' => null,
                'assignment_date' => null,
                'status' => 'In Stock',
                'last_status_date' => Carbon::now()->subDays(60),
                'notes' => 'HP LaserJet Pro M404dn'
            ],
            [
                'asset_tag' => 'SAI-MON-0222',
                'serial_number' => 'SN888999000',
                'asset_type_id' => $monitor->id,
                'assigned_to' => $employee1->id,
                'assignment_date' => Carbon::now()->subDays(10),
                'status' => 'In Use',
                'last_status_date' => Carbon::now()->subDays(10),
                'notes' => 'Dell 24" UltraSharp U2422H'
            ],
        ];

        foreach ($assets as $asset) {
            Asset::create($asset);
        }
    }
}