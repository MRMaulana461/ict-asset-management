<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'employee_id',
        'user_id',
        'name',
        'email',
        'department',
        'cost_center',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationship: Employee memiliki banyak assets yang di-assign
    public function assets()
    {
        return $this->hasMany(Asset::class, 'assigned_to');
    }

    // Relationship: Employee memiliki banyak loan history
    public function loanLogs()
    {
        return $this->hasMany(LoanLog::class, 'borrower_id');
    }

    // Relationship: Employee memiliki banyak asset assignment history
    public function assetHistories()
    {
        return $this->hasMany(AssetHistory::class);
    }

    // Scope untuk employee aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}