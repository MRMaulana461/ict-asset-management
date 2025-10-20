<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $fillable = [
        'asset_tag',
        'serial_number',
        'asset_type_id',
        'assigned_to',
        'assignment_date',
        'status',
        'last_status_date',
        'notes',
        'quantity'
    ];

    protected $casts = [
        'last_status_date' => 'date',
        'assignment_date' => 'date',
    ];

    // Relationship: Asset belongs to AssetType
    public function assetType()
    {
        return $this->belongsTo(AssetType::class);
    }

    // App\Models\Asset.php
    public function assignedEmployee()
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }

    // Relationship: Asset memiliki banyak loan logs
    public function loanLogs()
    {
        return $this->hasMany(LoanLog::class);
    }

    // Relationship: Asset memiliki banyak assignment histories
    public function assetHistories()
    {
        return $this->hasMany(AssetHistory::class);
    }

    // Scopes untuk filter status
    public function scopeInStock($query)
    {
        return $query->where('status', 'In Stock');
    }

    public function scopeInUse($query)
    {
        return $query->where('status', 'In Use');
    }

    public function scopeBroken($query)
    {
        return $query->where('status', 'Broken');
    }

    public function scopeRetired($query)
    {
        return $query->where('status', 'Retired');
    }

    public function scopeTaken($query)
    {
        return $query->where('status', 'Taken');
    }

    // Helper method untuk check availability
    public function isAvailable()
    {
        return $this->status === 'In Stock';
    }
}