<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisaApplication extends Model
{
    protected $fillable = [
    'application_no',
    'customer_id',
    'country_id',
    'visa_type_id',
    'passport_no',
    'passport_received_date',
    'passport_return_date',
    'passport_status',
    'travel_date',
    'return_date',
    'visa_fee',
    'service_fee',
    'total_amount',
    'paid_amount',
    'balance_amount',
    'status',
    'notes',
];

    public function customer()
{
    return $this->belongsTo(\App\Models\Customer::class);
}

public function country()
{
    return $this->belongsTo(\App\Models\Country::class);
}

public function visaType()
{
    return $this->belongsTo(\App\Models\VisaType::class);
}
public function payments()
{
    return $this->hasMany(Payment::class);
}
}