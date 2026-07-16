<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerNotificationRead extends Model
{
    protected $fillable = [
        'customer_id',
        'customer_notification_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function notification(): BelongsTo
    {
        return $this->belongsTo(CustomerNotification::class, 'customer_notification_id');
    }
}