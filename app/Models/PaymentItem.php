<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentItem extends Model
{
    protected $connection = 'kaelco';
    protected $table = 'payment_items';

    // Allow mass assignment
    protected $fillable = [
        'payment_id',
        'item_type',
        'reference_id',
        'amount',
    ];

    // If timestamps exist (created_at/updated_at), keep this true
    public $timestamps = true;

    /*
     * Relationships (optional)
     * Uncomment if you have Bill model
     */
    // public function bill()
    // {
    //     return $this->belongsTo(Bill::class, 'bill_id');
    // }
}
