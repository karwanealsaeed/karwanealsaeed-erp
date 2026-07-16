<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlightPayment extends Model
{
    protected $fillable = [
        'flight_booking_id',
        'payment_date',
        'amount',
        'payment_method',
        'reference_no',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
    ];

    public function flightBooking()
    {
        return $this->belongsTo(FlightBooking::class);
    }

    public static function recalculateBookingAmounts(?int $flightBookingId): void
    {
        if (! $flightBookingId) {
            return;
        }

        $booking = FlightBooking::find($flightBookingId);

        if (! $booking) {
            return;
        }

        $paidAmount = self::where('flight_booking_id', $flightBookingId)->sum('amount');

        $booking->update([
            'paid_amount' => $paidAmount,
            'balance_amount' => max((float) $booking->total_amount - (float) $paidAmount, 0),
        ]);
    }
}