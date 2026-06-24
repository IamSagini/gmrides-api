<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model {
    protected $fillable = [
        'booking_id','payment_method','transaction_id',
        'gateway_reference','amount','currency','status',
        'gateway_response','paid_at'
    ];

    protected $casts = ['gateway_response' => 'array', 'paid_at' => 'datetime'];

    public function booking() {
        return $this->belongsTo(Booking::class);
    }
}
