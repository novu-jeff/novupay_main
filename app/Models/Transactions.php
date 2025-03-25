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
        'payment_id',
        'by_method',
        'content',
        'status',
        'request',
        'callback'
    ];

}
