<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Airport;
use App\Models\City;
use Illuminate\Validation\ValidationException;

final class AirportObserver
{
    public function saving(Airport $airport): void
    {
        if (blank($airport->city_id)) {
            return;
        }

        $city =
            City::query()
                ->find($airport->city_id);

        if (! $city instanceof City) {
            throw ValidationException::withMessages([
                'city_id' =>
                    'Select a valid city.',
            ]);
        }

        if (
            (int) $city->country_id
            !== (int) $airport->country_id
        ) {
            throw ValidationException::withMessages([
                'city_id' =>
                    'Selected city does not belong to the selected country.',
            ]);
        }

        $airport->city = $city->name;
    }
}
