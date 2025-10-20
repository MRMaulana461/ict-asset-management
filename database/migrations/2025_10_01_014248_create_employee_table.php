<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 50)->unique()->comment('ID dari sistem global Saipem');
            $table->string('user_id', 50)->nullable();
            $table->string('name', 100);
            $table->string('email', 100)->unique();
            $table->string('department', 100)->nullable();
            $table->string('cost_center', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->index('employee_id');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};