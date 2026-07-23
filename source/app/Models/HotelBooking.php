<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelBooking extends Model
{
    protected $fillable = [
        'booking_no',
        'customer_id',
        'hotel_id',
        'city_id',
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

    protected static function booted(): void
    {
        static::saving(function (HotelBooking $booking): void {
            if (filled($booking->hotel_id)) {
                $hotel =
                    Hotel::query()
                        ->with('city.country')
                        ->whereKey($booking->hotel_id)
                        ->where('status', 'Active')
                        ->first();

                if (! $hotel instanceof Hotel) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'hotel_id' =>
                            'Please select an active Hotel Master record.',
                    ]);
                }

                $city = $hotel->city;

                if (
                    ! $city instanceof City
                    || $city->status !== 'Active'
                ) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'hotel_id' =>
                            'Selected Hotel does not have an active City Master.',
                    ]);
                }

                $booking->city_id = $city->id;
                $booking->country_id = $city->country_id;
                $booking->city = $city->name;
                $booking->hotel_name = $hotel->hotel_name;

                return;
            }

            if (filled($booking->city_id)) {
                $city =
                    City::query()
                        ->whereKey($booking->city_id)
                        ->where('status', 'Active')
                        ->first();

                if (! $city instanceof City) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'city_id' =>
                            'Please select an active City Master record.',
                    ]);
                }

                $booking->country_id = $city->country_id;
                $booking->city = $city->name;
            }

            $errors = [];

            if (blank($booking->country_id)) {
                $errors['country_id'] =
                    'Country is required for a Hotel Booking.';
            }

            if (blank($booking->city)) {
                $errors['city'] =
                    'City is required for a Hotel Booking.';
            }

            if (blank($booking->hotel_name)) {
                $errors['hotel_name'] =
                    'Hotel name is required for a Hotel Booking.';
            }

            if ($errors !== []) {
                throw \Illuminate\Validation\ValidationException::withMessages(
                    $errors,
                );
            }
        });
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function cityMaster()
    {
        return $this->belongsTo(
            City::class,
            'city_id',
        );
    }

    public function customer()    {
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