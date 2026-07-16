<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlightBooking extends Model
{
    protected $fillable = [
        'booking_no',
        'customer_id',
        'airline_id',
        'from_airport_id',
        'to_airport_id',
        'pnr',
        'ticket_no',
        'departure_date',
        'return_date',
        'trip_type',
        'class',
        'fare',
        'tax',
        'service_fee',
        'total_amount',
        'paid_amount',
        'balance_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'return_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function airline()
    {
        return $this->belongsTo(Airline::class);
    }

    public function fromAirport()
    {
        return $this->belongsTo(Airport::class, 'from_airport_id');
    }

    public function toAirport()
    {
        return $this->belongsTo(Airport::class, 'to_airport_id');
    }

    public function payments()
    {
        return $this->hasMany(FlightPayment::class);
    }
}