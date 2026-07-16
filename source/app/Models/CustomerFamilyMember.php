<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerFamilyMember extends Model
{
    protected $fillable = [
        'customer_id',
        'name',
        'relationship',
        'cnic',
        'passport_no',
        'date_of_birth',
        'mobile',
        'status',
        'notes',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}