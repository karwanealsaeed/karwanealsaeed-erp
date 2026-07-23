<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Hotel extends Model
{
    protected $fillable = [
        'hotel_name',
        'city_id',
        'supplier_id',
        'star_rating',
        'address',
        'contact_no',
        'email',
        'website',
        'status',
        'notes',
    ];

    protected $casts = [
        'star_rating' => 'integer',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(
            City::class,
        );
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(
            Supplier::class,
        );
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(
            HotelBooking::class,
        );
    }

    public function getDisplayNameAttribute(): string
    {
        return trim(
            $this->hotel_code
            .' - '
            .$this->hotel_name
            .' - '
            .($this->city?->name ?: '-'),
        );
    }
}