<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('asset_type_id');
            $table->integer('quantity')->default(1);
            $table->text('reason');
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('asset_type_id')->references('id')->on('asset_types')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};