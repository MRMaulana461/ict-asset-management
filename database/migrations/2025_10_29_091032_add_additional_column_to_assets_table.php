<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('assets', function (Blueprint $table) {
            // Excel specific columns
            $table->string('code', 50)->nullable()->after('id');
            $table->string('email_address', 100)->nullable()->after('username');
            $table->string('cost_center', 50)->nullable()->after('dept_project');
            $table->string('location_site', 150)->nullable()->after('location');
            $table->string('assignment_status', 50)->nullable()->after('assignment_date');
            $table->string('soc_compliant', 10)->nullable()->after('remarks');
            $table->string('ref', 50)->nullable()->after('po_ref');
            $table->string('memory', 50)->nullable()->after('device_name');
            
            // Add indexes
            $table->index('code');
            $table->index('email_address');
            $table->index('cost_center');
            $table->index('assignment_status');
        });
    }

    public function down()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropIndex(['code']);
            $table->dropIndex(['email_address']);
            $table->dropIndex(['cost_center']);
            $table->dropIndex(['assignment_status']);
            
            $table->dropColumn([
                'code', 'email_address', 'cost_center', 'location_site',
                'assignment_status', 'soc_compliant', 'ref', 'memory'
            ]);
        });
    }
};