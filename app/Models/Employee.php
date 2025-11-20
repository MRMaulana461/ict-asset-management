<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{

    protected $fillable = [
        // Basic Info
        'ghrs_id',
        'empl_rcd',
        'badge_id',
        'user_id',
        'name',
        'first_name',
        'last_name',
        'email',
        'is_active',

        // Company & Org
        'company',
        'org_context',
        'org_relation',
        'role_company',
        'employee_class',
        'dept_id',
        'department',
        'agency',
        'boc',
        'cost_center',
        'cost_center_descr',

        // Project
        'project_id',
        'project_description',

        // Contract Info
        'contract_type',
        'contract_number',
        'contractual_position',
        'tipo_terzi',
        'hire_date',
        'expiry_date',
        'first_start_date',

        // Job Info
        'job_code',
        'job_title',
        'supervisor_name',
        'supervisor_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function assets()
    {
        return $this->hasMany(Asset::class, 'assigned_to');
    }

    public function loanLogs()
    {
        return $this->hasMany(LoanLog::class, 'borrower_id');
    }

    public function assetHistories()
    {
        return $this->hasMany(AssetHistory::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}