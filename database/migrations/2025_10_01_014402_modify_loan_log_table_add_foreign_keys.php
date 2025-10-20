<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 0: Tambahkan kolom is_active ke employees
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('cost_center');
            }
        });

        // Step 1: Tambah kolom borrower_id & asset_id ke loan_log
        Schema::table('loan_log', function (Blueprint $table) {
            $table->unsignedBigInteger('borrower_id')->nullable()->after('id');
            $table->unsignedBigInteger('asset_id')->nullable()->after('borrower_id');
        });

        // Step 2: Migrasi pic_user → employees
        $borrowers = DB::table('loan_log')
            ->distinct()
            ->pluck('pic_user');

        foreach ($borrowers as $borrower) {
            $existingEmployee = DB::table('employees')->where('name', $borrower)->first();

            if (!$existingEmployee) {
                $employeeId = 'TEMP' . str_pad(DB::table('employees')->count() + 1, 4, '0', STR_PAD_LEFT);

                DB::table('employees')->insert([
                    'employee_id'   => $employeeId,
                    'name'          => $borrower,
                    'email'         => strtolower(str_replace(' ', '.', $borrower)) . '@temp.local',
                    'department'    => 'Not Available',
                    'cost_center'   => null,
                    'is_active'     => true,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            $employee = DB::table('employees')->where('name', $borrower)->first();

            DB::table('loan_log')
                ->where('pic_user', $borrower)
                ->update(['borrower_id' => $employee->id]);
        }

        // Step 3: Migrasi item_description → assets
        $loans = DB::table('loan_log')->get();

        foreach ($loans as $loan) {
            $itemName = trim($loan->item_description);

            $assetType = DB::table('asset_types')
                ->where('name', 'LIKE', "%{$itemName}%")
                ->first();

            if (!$assetType) {
                $assetType = DB::table('asset_types')->where('name', 'Mouse')->first();
            }

            $asset = DB::table('assets')->where('asset_tag', 'LOAN-' . $loan->id)->first();

            if (!$asset) {
                DB::table('assets')->insert([
                    'asset_tag'        => 'LOAN-' . $loan->id,
                    'serial_number'    => null,
                    'asset_type_id'    => $assetType->id,
                    'assigned_to'      => null,
                    'status'           => 'In Stock',
                    'last_status_date' => $loan->loan_date,
                    'notes'            => 'Migrated from loan_log: ' . $loan->item_description,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);

                $asset = DB::table('assets')->where('asset_tag', 'LOAN-' . $loan->id)->first();
            }

            DB::table('loan_log')
                ->where('id', $loan->id)
                ->update(['asset_id' => $asset->id]);
        }

        // Step 4: Tambah foreign key & hapus kolom lama
        Schema::table('loan_log', function (Blueprint $table) {
            $table->foreign('borrower_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('asset_id')->references('id')->on('assets')->onDelete('cascade');
        });

        Schema::table('loan_log', function (Blueprint $table) {
            $table->dropColumn(['pic_user', 'item_description']);
        });
    }

    public function down(): void
    {
        Schema::table('loan_log', function (Blueprint $table) {
            $table->dropForeign(['borrower_id']);
            $table->dropForeign(['asset_id']);
            $table->dropColumn(['borrower_id', 'asset_id']);

            $table->string('pic_user', 100)->after('loan_time');
            $table->string('item_description', 150)->after('pic_user');
        });

        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
