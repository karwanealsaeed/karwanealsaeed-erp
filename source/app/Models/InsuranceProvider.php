<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InsuranceProvider extends Model
{
    protected $fillable = [
        'supplier_id',
        'name',
        'company_name',
        'contact_person',
        'phone',
        'whatsapp',
        'email',
        'website',
        'address',
        'city',
        'country',
        'license_no',
        'status',
        'notes',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(
            Supplier::class,
        );
    }

    public function policies(): HasMany
    {
        return $this->hasMany(
            TravelInsurancePolicy::class,
        );
    }

    public function scopeActive(
        Builder $query,
    ): Builder {
        return $query->where(
            'status',
            'Active',
        );
    }

    public function getDisplayNameAttribute(): string
    {
        return filled(
            $this->company_name,
        )
            ? (string) $this->company_name
            : (string) $this->name;
    }
}