<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_no',
        'customer_id',
        'invoice_date',
        'due_date',
        'service_type',
        'service_reference',
        'sub_total',
        'discount',
        'tax',
        'total_amount',
        'paid_amount',
        'balance_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
public function customerPaymentUploads(): HasMany
{
    return $this->hasMany(
        CustomerPaymentUpload::class
    );
}
}