<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LoanLog extends Model
{
    protected $table = 'loan_log';
    
    public $timestamps = false;
    
    protected $fillable = [
        'borrower_id',
        'asset_id',
        'asset_type_id',
        'loan_date',
        'loan_time',
        'quantity',
        'duration_days',
        'return_date',
        'status',
        'reason'
    ];

    protected $casts = [
        'loan_date' => 'date',
        'return_date' => 'date',
        'created_at' => 'datetime'
    ];

    // Relationship: LoanLog belongs to Employee (borrower)
    public function borrower()
    {
        return $this->belongsTo(Employee::class, 'borrower_id');
    }

    // Relationship: LoanLog belongs to Asset
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    // Relationship: LoanLog belongs to AssetType (untuk data historis)
    public function assetType()
    {
        return $this->belongsTo(AssetType::class, 'asset_type_id');
    }

    // Scopes
    public function scopeOnLoan($query)
    {
        return $query->where('status', 'On Loan');
    }

    public function scopeReturned($query)
    {
        return $query->where('status', 'Returned');
    }

    // Helper method
    public function isReturned()
    {
        return $this->status === 'Returned';
    }

    public function getDurationDays()
    {
        if (!$this->return_date) {
            return now()->diffInDays($this->loan_date);
        }
        return $this->return_date->diffInDays($this->loan_date);
    }

    // New methods for due date calculations
    public function getDueDateAttribute()
    {
        return Carbon::parse($this->loan_date)->addDays($this->duration_days);
    }

    public function getDaysRemainingAttribute()
    {
        return now()->diffInDays($this->due_date, false);
    }

    public function getIsOverdueAttribute()
    {
        return $this->status == 'On Loan' && $this->days_remaining < 0;
    }

    public function getIsDueTodayAttribute()
    {
        return $this->status == 'On Loan' && $this->days_remaining == 0;
    }

    public function getIsDueSoonAttribute()
    {
        return $this->status == 'On Loan' && $this->days_remaining == 1;
    }

    public function getDaysRemainingTextAttribute()
    {
        if ($this->days_remaining < 0) {
            return 'Overdue by ' . abs($this->days_remaining) . ' days';
        } elseif ($this->days_remaining == 0) {
            return 'Due Today';
        } else {
            return $this->days_remaining . ' days left';
        }
    }

    public function getStatusAlertAttribute()
    {
        if ($this->is_overdue) {
            return [
                'type' => 'error',
                'message' => 'OVERDUE: ' . abs($this->days_remaining) . ' day(s) late!',
                'class' => 'bg-red-100 border-red-400 text-red-700'
            ];
        } elseif ($this->is_due_today) {
            return [
                'type' => 'warning',
                'message' => 'DUE TODAY: Item should be returned today!',
                'class' => 'bg-orange-100 border-orange-400 text-orange-700'
            ];
        } elseif ($this->is_due_soon) {
            return [
                'type' => 'info',
                'message' => 'DUE TOMORROW: Item should be returned tomorrow!',
                'class' => 'bg-yellow-100 border-yellow-400 text-yellow-700'
            ];
        }

        return null;
    }
}