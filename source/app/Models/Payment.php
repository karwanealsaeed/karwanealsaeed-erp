<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'visa_application_id',
        'payment_date',
        'amount',
        'payment_method',
        'reference_no',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
    ];

    public function visaApplication()
    {
        return $this->belongsTo(VisaApplication::class);
    }
}