<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LoanLog;
use App\Models\Employee;
use App\Models\Asset;
use App\Models\AssetType;
use Carbon\Carbon;

class LoanLogSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create employees
        $emp1 = Employee::firstOrCreate(
            ['employee_id' => 'SAI003'],
            [
                'name' => 'Mahendra',
                'email' => 'mahendra@saipem.com',
                'department' => 'Operations',
                'is_active' => true
            ]
        );

        $emp2 = Employee::firstOrCreate(
            ['employee_id' => 'SAI004'],
            [
                'name' => 'Tristan',
                'email' => 'tristan@saipem.com',
                'department' => 'Engineering',
                'is_active' => true
            ]
        );

        // Get asset types
        $keyboard = AssetType::where('name', 'Keyboard')->first();
        $mouse = AssetType::where('name', 'Mouse')->first();

        // Create peripheral assets for loan
        $keyboardAsset = Asset::firstOrCreate(
            ['asset_tag' => 'PER-KEY-001'],
            [
                'serial_number' => 'KEY001',
                'asset_type_id' => $keyboard->id,
                'status' => 'In Stock',
                'last_status_date' => Carbon::now(),
                'notes' => 'Logitech K120'
            ]
        );

        $mouseAsset = Asset::firstOrCreate(
            ['asset_tag' => 'PER-MOU-001'],
            [
                'serial_number' => 'MOU001',
                'asset_type_id' => $mouse->id,
                'status' => 'In Stock',
                'last_status_date' => Carbon::now(),
                'notes' => 'Logitech M185'
            ]
        );

        $loans = [
            [
                'borrower_id' => $emp1->id,
                'asset_id' => $keyboardAsset->id,
                'loan_date' => Carbon::parse('2025-02-19'),
                'loan_time' => '09:45:00',
                'quantity' => 1,
                'return_date' => null,
                'status' => 'On Loan',
            ],
            [
                'borrower_id' => $emp2->id,
                'asset_id' => $mouseAsset->id,
                'loan_date' => Carbon::parse('2025-03-21'),
                'loan_time' => '10:10:00',
                'quantity' => 1,
                'return_date' => null,
                'status' => 'On Loan',
            ],
        ];

        foreach ($loans as $loan) {
            LoanLog::create($loan);
        }
    }
}