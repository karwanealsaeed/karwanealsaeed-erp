<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerNotification extends Model
{
    protected $fillable = [
        'customer_id',
        'title',
        'type',
        'service_type',
        'message',
        'action_label',
        'action_url',
        'is_read',
        'visible_from',
        'visible_until',
        'status',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'visible_from' => 'date',
        'visible_until' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}