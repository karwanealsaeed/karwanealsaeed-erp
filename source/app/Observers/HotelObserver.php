<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\City;
use App\Models\Hotel;
use App\Models\Supplier;
use App\Services\MasterCodeGenerator;
use Illuminate\Validation\ValidationException;

final class HotelObserver
{
    public function __construct(
        private readonly MasterCodeGenerator $codes,
    ) {
    }

    public function saving(Hotel $hotel): void
    {
        $hotel->hotel_name =
            trim(
                (string) $hotel->hotel_name,
            );

        if ($hotel->hotel_name === '') {
            throw ValidationException::withMessages([
                'hotel_name' =>
                    'Hotel name is required.',
            ]);
        }

        if (
            ! City::query()
                ->whereKey($hotel->city_id)
                ->where('status', 'Active')
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'city_id' =>
                    'Please select an active City Master.',
            ]);
        }

        if (
            filled($hotel->supplier_id)
            && ! Supplier::query()
                ->whereKey($hotel->supplier_id)
                ->where('status', 'Active')
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'supplier_id' =>
                    'Please select an active Supplier.',
            ]);
        }

        if (
            filled($hotel->star_rating)
            && (
                (int) $hotel->star_rating < 1
                || (int) $hotel->star_rating > 5
            )
        ) {
            throw ValidationException::withMessages([
                'star_rating' =>
                    'Star rating must be between 1 and 5.',
            ]);
        }

        if (filled($hotel->email)) {
            $hotel->email =
                strtolower(
                    trim(
                        (string) $hotel->email,
                    ),
                );
        }

        foreach (
            [
                'contact_no',
                'website',
            ]
            as $field
        ) {
            if (filled($hotel->{$field})) {
                $hotel->{$field} =
                    trim(
                        (string) $hotel->{$field},
                    );
            }
        }
    }

    public function creating(Hotel $hotel): void
    {
        if (blank($hotel->hotel_code)) {
            $hotel->hotel_code =
                $this->codes->nextHotelCode();
        }
    }

    public function updating(Hotel $hotel): void
    {
        if ($hotel->isDirty('hotel_code')) {
            throw ValidationException::withMessages([
                'hotel_code' =>
                    'Hotel code is generated automatically and cannot be changed.',
            ]);
        }
    }
}