<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'company_name',
        'mobile',
        'whatsapp',
        'email',
        'address',
        'city',
        'country',
        'supplier_type',
        'status',
        'notes',
    ];

    public function bills(): HasMany
    {
        return $this->hasMany(
            SupplierBill::class
        );
    }

    public function payments(): HasMany
    {
        return $this->hasMany(
            SupplierPayment::class
        );
    }

    public function getDisplayNameAttribute(): string
    {
        if (filled($this->company_name)) {
            return $this->company_name
                . ' - '
                . $this->name;
        }

        return (string) $this->name;
    }
}