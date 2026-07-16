<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelBooking extends Model
{
    protected $fillable = [
        'booking_no',
        'customer_id',
        'country_id',
        'city',
        'hotel_name',
        'check_in_date',
        'check_out_date',
        'no_of_rooms',
        'no_of_persons',
        'room_type',
        'total_amount',
        'paid_amount',
        'balance_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function payments()
    {
        return $this->hasMany(HotelPayment::class);
    }
}