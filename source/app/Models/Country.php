<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    protected $fillable = [
        'name',
        'code',
        'iso3',
        'currency',
        'phone_code',
        'status',
    ];

    public function airports(): HasMany
    {
        return $this->hasMany(Airport::class);
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }
}