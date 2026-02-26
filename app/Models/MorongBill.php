<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MorongBill extends Model
{
    protected $connection = 'morong';
    protected $table = 'morong_bills';

    protected $fillable = [
        'reference_no',
        'account_no',
        'payor',
        'email',
        'contact_no',
        'amount',
        'arrears',
        'previous_reading',
        'present_reading',
        'consumption',
        'due_date',
        'initiated_at',
        'paid_at',
        'hitpay_reference',
        'hitpay_url',
        'payload',
        'status',
        'is_high_consumption',
        'high_consumption_note',
        'hitpay_reference',
        'hitpay_url',
    ];

    protected $casts = [
        'amount' => 'float',
        'arrears' => 'float',
        'previous_reading' => 'integer',
        'present_reading' => 'integer',
        'consumption' => 'integer',
        'is_high_consumption' => 'boolean',
        'payload' => 'array',
        'due_date' => 'datetime',
        'initiated_at' => 'datetime',
        'paid_at' => 'datetime',
    ];
}
