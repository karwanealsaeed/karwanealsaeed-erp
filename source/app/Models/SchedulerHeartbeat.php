<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchedulerHeartbeat extends Model
{
    protected $fillable = [
        'monitor_key',
        'last_started_at',
        'last_finished_at',
        'last_success_at',
        'last_failed_at',
        'last_duration_ms',
        'last_exit_code',
        'consecutive_failures',
        'failure_message',
        'last_alerted_at',
    ];

    protected $casts = [
        'last_started_at' =>
            'datetime',

        'last_finished_at' =>
            'datetime',

        'last_success_at' =>
            'datetime',

        'last_failed_at' =>
            'datetime',

        'last_alerted_at' =>
            'datetime',

        'last_duration_ms' =>
            'integer',

        'last_exit_code' =>
            'integer',

        'consecutive_failures' =>
            'integer',
    ];
}