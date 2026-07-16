<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UmrahPayment extends Model
{
    protected $fillable = [
        'umrah_booking_id',
        'payment_date',
        'amount',
        'payment_method',
        'reference_no',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
    ];

    public function umrahBooking()
    {
        return $this->belongsTo(UmrahBooking::class);
    }

    public static function recalculateBookingAmounts(?int $umrahBookingId): void
    {
        if (! $umrahBookingId) {
            return;
        }

        $booking = UmrahBooking::find($umrahBookingId);

        if (! $booking) {
            return;
        }

        $paidAmount = self::where('umrah_booking_id', $umrahBookingId)->sum('amount');

        $booking->update([
            'paid_amount' => $paidAmount,
            'balance_amount' => max((float) $booking->package_amount - (float) $paidAmount, 0),
        ]);
    }
}