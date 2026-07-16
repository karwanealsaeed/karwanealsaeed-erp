<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UmrahTransportRoute extends Model
{
    protected $fillable = [
        'route_code',
        'title',
        'transport_type',
        'vehicle_type',
        'route_description',
        'notes',
        'status',
    ];

    public function packages(): HasMany
    {
        return $this->hasMany(UmrahPackage::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return trim(($this->route_code ? $this->route_code . ' - ' : '') . $this->title);
    }
}