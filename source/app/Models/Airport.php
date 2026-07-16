<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Airport extends Model
{
    protected $fillable = [
        'country_id',
        'name',
        'city',
        'iata_code',
        'icao_code',
        'status',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}