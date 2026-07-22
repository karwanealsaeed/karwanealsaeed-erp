<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\City;
use App\Models\Country;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class CityObserver
{
    public function saving(City $city): void
    {
        $city->name =
            trim((string) $city->name);

        if ($city->name === '') {
            throw ValidationException::withMessages([
                'name' =>
                    'City name is required.',
            ]);
        }

        if (
            ! Country::query()
                ->whereKey($city->country_id)
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'country_id' =>
                    'Select a valid country.',
            ]);
        }
    }

    public function creating(City $city): void
    {
        if (blank($city->code)) {
            $city->code =
                $this->generateCode($city);
        }
    }

    public function updating(City $city): void
    {
        if ($city->isDirty('code')) {
            throw ValidationException::withMessages([
                'code' =>
                    'City code is generated automatically and cannot be changed.',
            ]);
        }
    }

    private function generateCode(City $city): string
    {
        $country =
            Country::query()
                ->find($city->country_id);

        if (! $country instanceof Country) {
            throw ValidationException::withMessages([
                'country_id' =>
                    'Select a valid country.',
            ]);
        }

        $countryCode =
            strtoupper(
                trim(
                    (string)
                        (
                            $country->code
                            ?: $country->iso3
                            ?: 'XX'
                        ),
                ),
            );

        $slug =
            strtoupper(
                Str::slug(
                    (string) $city->name,
                    '-',
                ),
            );

        $base =
            trim(
                $countryCode.'-'.$slug,
                '-',
            );

        $code = $base;
        $suffix = 2;

        while (
            City::query()
                ->where('code', $code)
                ->exists()
        ) {
            $code =
                $base.'-'.$suffix;
            $suffix++;
        }

        return $code;
    }
}
