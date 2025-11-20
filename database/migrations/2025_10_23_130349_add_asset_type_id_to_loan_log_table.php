<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('loan_log', function (Blueprint $table) {
            
            $table->unsignedBigInteger('asset_type_id')->nullable()->after('asset_id');
            
            $table->foreign('asset_type_id')
                  ->references('id')
                  ->on('asset_types')
                  ->onDelete('set null');
        });
        
        DB::statement('
            UPDATE loan_log 
            JOIN assets ON loan_log.asset_id = assets.id 
            SET loan_log.asset_type_id = assets.asset_type_id
            WHERE loan_log.asset_id IS NOT NULL
        ');
    }

    public function down()
    {
        Schema::table('loan_log', function (Blueprint $table) {
            $table->dropForeign(['asset_type_id']);
            $table->dropColumn('asset_type_id');
        });
    }
};