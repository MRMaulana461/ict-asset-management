<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            // Job Information
            $table->string('job_code', 50)->nullable()->after('contractual_position');
            $table->string('job_title', 150)->nullable()->after('job_code');
            $table->string('supervisor_name', 150)->nullable()->after('job_title');
            $table->string('supervisor_id', 50)->nullable()->after('supervisor_name');
            
            // Contract Information
            $table->string('contract_type', 100)->nullable()->after('employee_class');
            $table->string('contract_number', 100)->nullable()->after('contract_type');
            $table->date('hire_date')->nullable()->after('contract_number');
            $table->date('expiry_date')->nullable()->after('hire_date');
            $table->date('first_start_date')->nullable()->after('expiry_date');
            
            // Location
            $table->string('location', 150)->nullable()->after('dept_id');
            $table->string('project_id', 50)->nullable()->after('location');
            $table->string('project_description', 200)->nullable()->after('project_id');
            
            // Add indexes
            $table->index('job_code');
            $table->index('supervisor_id');
            $table->index('location');
        });
    }

    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex(['job_code']);
            $table->dropIndex(['supervisor_id']);
            $table->dropIndex(['location']);
            
            $table->dropColumn([
                'job_code', 'job_title', 'supervisor_name', 'supervisor_id',
                'contract_type', 'contract_number', 'hire_date', 'expiry_date', 'first_start_date',
                'location', 'project_id', 'project_description'
            ]);
        });
    }
};