<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class Booking extends Model {
    protected $fillable = [
        'user_id','booking_reference','from_location','to_location','trip_type',
        'travel_date','departure_time','arrival_time','duration',
        'passengers','seats','passenger_name','passenger_email',
        'passenger_phone','fare_kes','total_kes','currency','total_paid','status',
        'discount_percent','discount_amount'
    ];
    protected $casts = ['seats' => 'array'];
    protected static function boot() {
        parent::boot();
        static::creating(function ($booking) {
            $booking->booking_reference = 'GMR-' . strtoupper(Str::random(6));
        });
    }
    public function payment() {
        return $this->hasOne(Payment::class);
    }
}
