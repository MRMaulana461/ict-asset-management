<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->enum('category', ['Hardware', 'Peripheral'])->default('Hardware');
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        // Insert default asset types
        DB::table('asset_types')->insert([
            ['name' => 'Laptop', 'category' => 'Hardware', 'created_at' => now()],
            ['name' => 'Desktop/PC', 'category' => 'Hardware', 'created_at' => now()],
            ['name' => 'Workstation', 'category' => 'Hardware', 'created_at' => now()],
            ['name' => 'Printer', 'category' => 'Hardware', 'created_at' => now()],
            ['name' => 'Monitor', 'category' => 'Hardware', 'created_at' => now()],
            ['name' => 'Keyboard', 'category' => 'Peripheral', 'created_at' => now()],
            ['name' => 'Mouse', 'category' => 'Peripheral', 'created_at' => now()],
            ['name' => 'Headphone', 'category' => 'Peripheral', 'created_at' => now()],
            ['name' => 'Speaker', 'category' => 'Peripheral', 'created_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_types');
    }
};