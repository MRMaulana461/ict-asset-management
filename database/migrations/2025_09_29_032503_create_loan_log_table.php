<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('loan_log', function (Blueprint $table) {
            $table->id();
            $table->date('loan_date');
            $table->time('loan_time')->nullable();
            $table->string('pic_user', 100);
            $table->string('item_description', 150);
            $table->integer('quantity')->default(1);
            $table->date('return_date')->nullable();
            $table->string('status', 20)->default('On Loan');
            $table->text('signature')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_log');
    }
};
