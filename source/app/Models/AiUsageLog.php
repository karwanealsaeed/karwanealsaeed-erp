<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsageLog extends Model
{
    protected $fillable = [
        'request_uuid',
        'session_token',
        'customer_id',
        'requested_provider',
        'provider',
        'model',
        'source',
        'language',
        'category',
        'status',
        'fallback_used',
        'latency_ms',
        'input_characters',
        'output_characters',
        'error_code',
        'error_message',
        'meta',
    ];

    protected $casts = [
        'customer_id' =>
            'integer',

        'fallback_used' =>
            'boolean',

        'latency_ms' =>
            'integer',

        'input_characters' =>
            'integer',

        'output_characters' =>
            'integer',

        'meta' =>
            'array',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(
            Customer::class,
        );
    }
}
