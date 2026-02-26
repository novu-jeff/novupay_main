<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KaelcoBill extends Model
{
    protected $connection = 'kaelco';
    protected $table = 'bills';

    protected $fillable = [
        'reference_no',
        'account_no',
        'payor',
        'email',
        'contact_no',
        'address',
        'amount',
        'disconnection_fee',
        'surcharge',
        'convenience_fee',
        'bill_month',
        'description',
        'status',
        'hitpay_reference',
        'hitpay_url',
        'initiated_at',
        'paid_at',
        'payload'
    ];
}


