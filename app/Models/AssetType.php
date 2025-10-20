<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetType extends Model
{
    protected $fillable = [
        'name',
        'category',
        'description'
    ];

    // Relationship: AssetType memiliki banyak assets
    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    // Scope untuk filter by category
    public function scopeHardware($query)
    {
        return $query->where('category', 'Hardware');
    }

    public function scopePeripheral($query)
    {
        return $query->where('category', 'Peripheral');
    }
}