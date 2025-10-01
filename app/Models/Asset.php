<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $fillable = [
        'asset_tag',
        'serial_number',
        'asset_type',
        'current_owner',
        'status',
        'last_status_date',
        'notes'
    ];

    protected $casts = [
        'last_status_date' => 'date',
    ];

    // Scope untuk filter status
    public function scopeNormal($query)
    {
        return $query->where('status', 'Normal');
    }

    public function scopeDamaged($query)
    {
        return $query->where('status', 'Rusak');
    }

    public function scopeOnLoan($query)
    {
        return $query->where('status', 'Dipinjam');
    }
}