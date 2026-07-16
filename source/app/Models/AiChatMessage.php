<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiChatMessage extends Model
{
    protected $fillable = [
        'session_token',
        'customer_id',
        'sender',
        'language',
        'category',
        'message',
        'needs_human_support',
        'meta',
    ];

    protected $casts = [
        'needs_human_support' => 'boolean',
        'meta' => 'array',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}