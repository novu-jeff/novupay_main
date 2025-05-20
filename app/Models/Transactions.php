<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transactions extends Model
{
    use HasFactory;

    protected $table = 'transactions';
    protected $fillable = [
        'reference_no',
        'amount',
        'payment_id',
        'by_method',
        'external_id',
        'operation_id',
        'date_paid',
    ];

}
