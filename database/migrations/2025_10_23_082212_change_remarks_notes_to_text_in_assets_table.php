<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            // Change to TEXT for unlimited length
            $table->text('remarks')->nullable()->change();
            $table->text('notes')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->string('remarks')->nullable()->change();
            $table->string('notes')->nullable()->change();
        });
    }
};