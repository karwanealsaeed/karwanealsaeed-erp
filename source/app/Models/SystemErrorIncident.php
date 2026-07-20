<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SystemErrorIncident extends Model
{
    protected $fillable = [
        'fingerprint',
        'severity',
        'environment',
        'exception_class',
        'message',
        'file',
        'line',
        'route_name',
        'request_method',
        'request_path',
        'user_id',
        'user_email',
        'occurrences',
        'first_seen_at',
        'last_seen_at',
        'last_notified_at',
        'resolved_at',
        'resolved_by',
        'resolution_note',
        'context',
        'sample_trace',
    ];

    protected $casts = [
        'line' =>
            'integer',

        'user_id' =>
            'integer',

        'occurrences' =>
            'integer',

        'resolved_by' =>
            'integer',

        'first_seen_at' =>
            'datetime',

        'last_seen_at' =>
            'datetime',

        'last_notified_at' =>
            'datetime',

        'resolved_at' =>
            'datetime',

        'context' =>
            'array',
    ];

    public function scopeUnresolved(
        Builder $query,
    ): Builder {
        return $query->whereNull(
            'resolved_at',
        );
    }

    public function scopeResolved(
        Builder $query,
    ): Builder {
        return $query->whereNotNull(
            'resolved_at',
        );
    }

    public function scopeSeverity(
        Builder $query,
        string $severity,
    ): Builder {
        return $query->where(
            'severity',
            $severity,
        );
    }

    public function getIsResolvedAttribute(): bool
    {
        return filled(
            $this->resolved_at,
        );
    }
}