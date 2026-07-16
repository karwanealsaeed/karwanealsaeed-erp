<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelPayment extends Model
{
    protected $fillable = [
        'hotel_booking_id',
        'payment_date',
        'amount',
        'payment_method',
        'reference_no',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
    ];

    public function hotelBooking()
    {
        return $this->belongsTo(HotelBooking::class);
    }

    public static function recalculateBookingAmounts(?int $hotelBookingId): void
    {
        if (! $hotelBookingId) {
            return;
        }

        $booking = HotelBooking::find($hotelBookingId);

        if (! $booking) {
            return;
        }

        $paidAmount = self::where('hotel_booking_id', $hotelBookingId)->sum('amount');

        $booking->update([
            'paid_amount' => $paidAmount,
            'balance_amount' => max((float) $booking->total_amount - (float) $paidAmount, 0),
        ]);
    }
}