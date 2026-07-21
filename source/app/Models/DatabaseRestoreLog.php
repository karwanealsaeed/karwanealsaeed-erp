<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatabaseRestoreLog extends Model
{
    protected $fillable = [
        'database_backup_id',
        'requested_by',
        'source_backup_no',
        'safety_backup_no',
        'database_name',
        'source_checksum',
        'status',
        'rollback_status',
        'started_at',
        'completed_at',
        'failed_at',
        'error_message',
        'context',
    ];

    protected $casts = [
        'database_backup_id' =>
            'integer',

        'requested_by' =>
            'integer',

        'started_at' =>
            'datetime',

        'completed_at' =>
            'datetime',

        'failed_at' =>
            'datetime',

        'context' =>
            'array',
    ];

    public function backup(): BelongsTo
    {
        return $this->belongsTo(
            DatabaseBackup::class,
            'database_backup_id',
        );
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'requested_by',
        );
    }
}