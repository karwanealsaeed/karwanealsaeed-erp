<?php

namespace App\Models;

use Illuminate\Validation\ValidationException;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UmrahHotel extends Model
{
    protected $fillable = [
        'city_id',
        'city',
        'hotel_name',
        'has_similar',
        'similar_text',
        'distance',
        'location',
        'meal',
        'rating',
        'contact_no',
        'notes',
        'status',
    ];

    protected $casts = [
        'has_similar' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (UmrahHotel $record): void {
            if (filled($record->city_id)) {
                $city = City::query()
                    ->where('id', $record->city_id)
                    ->where('status', 'Active')
                    ->first();

                if (! $city) {
                    throw ValidationException::withMessages([
                        'city_id' => 'Please select an active city.',
                    ]);
                }

                $record->city = $city->name;
            }
        });


        static::saving(function (UmrahHotel $hotel): void {
            if ($hotel->has_similar) {
                $hotel->similar_text = 'OR SIMILAR';
            } else {
                $hotel->similar_text = null;
            }

            if (filled($hotel->distance)) {
                $distance = strtoupper(trim((string) $hotel->distance));
                $distance = str_replace(['METER', 'METERS', 'MTR'], '', $distance);
                $distance = trim($distance);

                $hotel->distance = $distance;
            }
        });
    }

    public function makkahPackages(): HasMany
    {
        return $this->hasMany(UmrahPackage::class, 'makkah_hotel_id');
    }

    public function madinaPackages(): HasMany
    {
        return $this->hasMany(UmrahPackage::class, 'madina_hotel_id');
    }

    public function cityMaster(): BelongsTo
    {
        return $this->belongsTo(
            City::class,
            'city_id',
        );
    }
    public function getDisplayNameAttribute(): string
    {
        $similar = $this->has_similar ? ' OR SIMILAR' : '';

        return $this->hotel_name . $similar . ' - ' . $this->city;
    }

    public function getDistanceTextAttribute(): string
    {
        if (blank($this->distance)) {
            return '-';
        }

        return $this->distance . ' METER';
    }

    public function getHotelFullNameAttribute(): string
    {
        return trim($this->hotel_name . ($this->has_similar ? ' OR SIMILAR' : ''));
    }
}