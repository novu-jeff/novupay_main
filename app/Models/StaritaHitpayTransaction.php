<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaritaHitpayTransaction extends Model
{
    protected $connection = 'starita';
    protected $table = 'starita_hitpay_transactions';

    protected $fillable = [
        'reference_no',
        'hitpay_id',
        'payment_request_id',
        'payment_url',
        'amount',
        'convenience_fee',
        'final_amount',
        'status',
        'request_payload',
        'response_payload',
        'initiated_at',
        'paid_at',
    ];

    protected $casts = [
        'request_payload'  => 'array',
        'response_payload' => 'array',
        'initiated_at' => 'datetime',
        'paid_at' => 'datetime',
    ];
}
