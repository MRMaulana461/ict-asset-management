<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $fillable = [
        // Basic Identity
        'code',
        'asset_tag',
        'serial_number',
        'serial_clean',
        'service_tag',

        // Asset Type & Assignment
        'asset_type_id',
        'assigned_to',
        'assignment_date',
        'assignment_status',

        // Status
        'status',
        'last_status_date',

        // Purchase Info
        'pr_ref',
        'po_ref',
        'ref',
        'delivery_date',

        // Item Details
        'item_name',
        'brand',
        'type',
        'memory',
        'specifications',

        // Employee Info
        'ghrs_id',
        'badge_id',
        'username',
        'email_address',

        // Location & Department
        'location',
        'location_site',
        'dept_project',
        'cost_center',

        // Compliance
        'soc_compliant',

        // Computer Specific
        'device_name',

        // Notes
        'notes',
        'remarks',
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