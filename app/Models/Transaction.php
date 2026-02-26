<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transactions';

    protected $fillable = [
        'reference_no',       // internal or bill reference number
        'account_no',         // user/customer account number
        'amount',             // total amount charged (including fees)
        'base_amount',        // optional: before fees
        'service_fee',        // total service/processing fee
        'payment_id',         // local or HitPay payment ID
        'external_id',        // external gateway transaction id
        'operation_id',       // internal operation ref (optional)
        'by_method',          // gcash, qrph, etc.
        'payment_status',     // pending, paid, failed, refunded
        'date_paid',          // timestamp of successful payment
        'payer_name',         // name of customer
        'payer_email',        // email of customer
        'payer_contact',      // optional contact/mobile
        'callback_url',       // if applicable (redirect target)
        'response_payload',   // JSON of full gateway response
    ];

    protected $casts = [
        'response_payload' => 'array',
        'date_paid' => 'datetime',
    ];
}
