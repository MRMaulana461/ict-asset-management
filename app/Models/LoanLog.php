<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanLog extends Model
{
    protected $table = 'loan_log';

    public $timestamps = false ;

    protected $fillable = [
        'loan_date',
        'loan_time',
        'pic_user',
        'item_description',
        'quantity',
        'return_date',
        'status',
        'signature'
    ];

    protected $casts = [
        'loan_date' => 'date',
        'return_date' => 'date',
    ];

    // Scope untuk filter status
    public function scopeOnLoan($query)
    {
        return $query->where('status', 'On Loan');
    }

    public function scopeReturned($query)
    {
        return $query->where('status', 'Returned');
    }
}
