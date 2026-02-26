<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_no', 'account_no', 'payor', 'email', 'contact_no',
        'amount', 'arrears', 'due_date',
        'prev_reading', 'present_reading',
        'billing_period_from', 'billing_period_to',
        'status', 'hitpay_reference', 'hitpay_url',
        'payload', 'initiated_at', 'paid_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'initiated_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

}
