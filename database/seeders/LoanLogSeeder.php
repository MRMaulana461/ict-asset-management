<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LoanLog;
use Carbon\Carbon;

class LoanLogSeeder extends Seeder
{
    public function run(): void
    {
        $loans = [
            [
                'loan_date' => Carbon::parse('2025-02-19'),
                'loan_time' => '09:45:00',
                'pic_user' => 'Mahendra',
                'item_description' => 'Keyboard',
                'quantity' => 1,
                'return_date' => null,
                'status' => 'On Loan',
            ],
            [
                'loan_date' => Carbon::parse('2025-03-21'),
                'loan_time' => '10:10:00',
                'pic_user' => 'Tristan',
                'item_description' => 'Mouse',
                'quantity' => 1,
                'return_date' => null,
                'status' => 'On Loan',
            ],
            [
                'loan_date' => Carbon::parse('2025-07-24'),
                'loan_time' => '11:11:00',
                'pic_user' => 'Ryan Ahmad H.',
                'item_description' => 'Mouse',
                'quantity' => 1,
                'return_date' => Carbon::parse('2025-07-25'),
                'status' => 'Returned',
            ],
            [
                'loan_date' => Carbon::parse('2025-07-28'),
                'loan_time' => '09:15:00',
                'pic_user' => 'Agung C.',
                'item_description' => 'Mouse',
                'quantity' => 1,
                'return_date' => Carbon::parse('2025-07-29'),
                'status' => 'Returned',
            ],
        ];

        foreach ($loans as $loan) {
            LoanLog::create($loan);
        }
    }
}