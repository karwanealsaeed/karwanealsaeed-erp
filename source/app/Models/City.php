<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class City extends Model
{
    protected $fillable = [
        'country_id',
        'name',
        'code',
        'status',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function airports(): HasMany
    {
        return $this->hasMany(
            Airport::class,
            'city_id',
        );
    }
}
