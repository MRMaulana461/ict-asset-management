<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Tambah kolom baru TANPA foreign key dulu
        Schema::table('assets', function (Blueprint $table) {
            $table->unsignedBigInteger('asset_type_id')->nullable()->after('serial_number');
            $table->unsignedBigInteger('assigned_to')->nullable()->after('asset_type_id');
            $table->date('assignment_date')->nullable()->after('assigned_to');
        });

        // Step 2: Migrate data dari asset_type (string) ke asset_type_id (FK)
        $assets = DB::table('assets')->get();
        
        foreach ($assets as $asset) {
            $assetType = DB::table('asset_types')
                ->where('name', $asset->asset_type)
                ->first();
            
            if ($assetType) {
                DB::table('assets')->where('id', $asset->id)
                    ->update(['asset_type_id' => $assetType->id]);
            } else {
                // Jika tidak ada match, set ke "Laptop" sebagai default
                $defaultType = DB::table('asset_types')->where('name', 'Laptop')->first();
                DB::table('assets')->where('id', $asset->id)
                    ->update(['asset_type_id' => $defaultType->id]);
            }
        }

        // Step 3: Migrate current_owner ke employees (dummy data)
        $owners = DB::table('assets')
            ->whereNotNull('current_owner')
            ->where('current_owner', '!=', '')
            ->distinct()
            ->pluck('current_owner');
        
        foreach ($owners as $owner) {
            // Cek apakah employee sudah ada
            $existingEmployee = DB::table('employees')->where('name', $owner)->first();
            
            if (!$existingEmployee) {
                $employeeId = 'TEMP' . str_pad(DB::table('employees')->count() + 1, 4, '0', STR_PAD_LEFT);
                
                DB::table('employees')->insert([
                    'employee_id' => $employeeId,
                    'user_id' => null,
                    'name' => $owner,
                    'email' => strtolower(str_replace(' ', '.', $owner)) . '@temp.local',
                    'department' => 'Not Available',
                    'cost_center' => null,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            
            // Update assigned_to
            $employee = DB::table('employees')->where('name', $owner)->first();
            
            DB::table('assets')
                ->where('current_owner', $owner)
                ->update([
                    'assigned_to' => $employee->id,
                    'assignment_date' => DB::raw('last_status_date')
                ]);
        }

        // Step 4: Sekarang baru tambah foreign key constraints
        Schema::table('assets', function (Blueprint $table) {
            $table->foreign('asset_type_id')->references('id')->on('asset_types');
            $table->foreign('assigned_to')->references('id')->on('employees')->onDelete('set null');
        });

        // Step 5: Hapus kolom lama
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['asset_type', 'current_owner', 'status']);
        });

        // Step 6: Tambah kolom status baru
        Schema::table('assets', function (Blueprint $table) {
            $table->enum('status', ['In Stock', 'In Use', 'Broken', 'Retired', 'Taken'])
                  ->default('In Stock')
                  ->after('assigned_to');
        });

        // Step 7: Update status lama ke status baru
        DB::table('assets')->where('status', null)->update(['status' => 'In Stock']);
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['asset_type_id']);
            $table->dropForeign(['assigned_to']);
            $table->dropColumn(['asset_type_id', 'assigned_to', 'assignment_date', 'status']);
            
            $table->string('asset_type', 50)->after('serial_number');
            $table->string('current_owner', 100)->nullable()->after('asset_type');
            $table->string('status', 20)->default('Normal')->after('current_owner');
        });
    }
};