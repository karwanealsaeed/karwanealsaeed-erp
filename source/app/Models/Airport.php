<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\Model;

class Airport extends Model
{
    protected $fillable = [
        'country_id',
        'name',
        'city',
        'city_id',
        'iata_code',
        'icao_code',
        'status',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function cityMaster(): BelongsTo
    {
        return $this->belongsTo(
            City::class,
            'city_id',
        );
    }
}