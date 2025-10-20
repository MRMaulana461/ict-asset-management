<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // HAPUS SEMUA DATA LAMA
        DB::table('loan_log')->truncate();

        // Step 0: Pastikan kolom yang diperlukan ada di tabel employees
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'sam_account_name')) {
                $table->string('sam_account_name', 100)->nullable()->after('employee_id');
            }
            if (!Schema::hasColumn('employees', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('cost_center');
            }
        });

        // Step 1: Drop kolom lama jika ada
        Schema::table('loan_log', function (Blueprint $table) {
            // Drop foreign keys dulu jika ada
            if (Schema::hasColumn('loan_log', 'borrower_id')) {
                $table->dropForeign(['borrower_id']);
            }
            if (Schema::hasColumn('loan_log', 'asset_id')) {
                $table->dropForeign(['asset_id']);
            }
            
            // Drop kolom lama
            $columnsToDrop = ['pic_user', 'item_description', 'borrower_id', 'asset_id','signature'];
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('loan_log', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        // Step 2: Tambah kolom baru sesuai struktur yang benar
        Schema::table('loan_log', function (Blueprint $table) {
            // Kolom sesuai screenshot
            $table->unsignedBigInteger('borrower_id')->after('id');
            $table->unsignedBigInteger('asset_id')->after('borrower_id');
            
            // Pastikan kolom lain ada
            if (!Schema::hasColumn('loan_log', 'loan_date')) {
                $table->date('loan_date')->after('asset_id');
            }
            if (!Schema::hasColumn('loan_log', 'loan_time')) {
                $table->time('loan_time')->after('loan_date');
            }
            if (!Schema::hasColumn('loan_log', 'quantity')) {
                $table->integer('quantity')->default(1)->after('loan_time');
            }
            if (!Schema::hasColumn('loan_log', 'duration_days')) {
                $table->integer('duration_days')->default(1)->after('quantity');
            }
            if (!Schema::hasColumn('loan_log', 'return_date')) {
                $table->date('return_date')->nullable()->after('duration_days');
            }
            if (!Schema::hasColumn('loan_log', 'status')) {
                $table->string('status', 50)->default('Borrowed')->after('return_date');
            }
            if (!Schema::hasColumn('loan_log', 'reason')) {
                $table->text('reason')->nullable()->after('status');
            }
        });

        // Step 3: Tambah foreign key
        Schema::table('loan_log', function (Blueprint $table) {
            $table->foreign('borrower_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('asset_id')->references('id')->on('assets')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('loan_log', function (Blueprint $table) {
            // Drop foreign keys
            $table->dropForeign(['borrower_id']);
            $table->dropForeign(['asset_id']);
            
            // Drop kolom baru
            $table->dropColumn([
                'borrower_id', 
                'asset_id',
                'duration_days',
                'return_date',
                'status',
                'reason'
            ]);
            
            // Kembalikan kolom lama
            $table->string('pic_user', 100)->after('loan_time');
            $table->string('item_description', 150)->after('pic_user');
        });

        // Rollback kolom employees
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'sam_account_name')) {
                $table->dropColumn('sam_account_name');
            }
        });
    }
};