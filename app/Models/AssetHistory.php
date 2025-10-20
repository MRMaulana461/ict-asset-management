<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetHistory extends Model
{
    public $timestamps = false; // Hanya created_at
    
    protected $fillable = [
        'asset_id',
        'employee_id',
        'assignment_date',
        'return_date',
        'notes'
    ];

    protected $casts = [
        'assignment_date' => 'date',
        'return_date' => 'date',
    ];

    // Relationship: History belongs to Asset
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    // Relationship: History belongs to Employee
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Scope untuk active assignment (belum return)
    public function scopeActive($query)
    {
        return $query->whereNull('return_date');
    }

    // Scope untuk completed assignment
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('return_date');
    }
}