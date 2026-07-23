<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'father_name',
        'cnic',
        'passport_no',
        'mobile',
        'whatsapp',
        'email',
        'date_of_birth',
        'address',
        'city',
        'country',
        'customer_type',
        'status',
        'notes',
        'agency_name',
        'commission_rate',
        'credit_limit',
        'portal_password',
        'portal_registered_at',
        'last_portal_login_at',
    ];

    protected $hidden = [
        'portal_password',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'date_of_birth' => 'date',
        'portal_registered_at' => 'datetime',
        'last_portal_login_at' => 'datetime',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(CustomerDocument::class);
    }
}