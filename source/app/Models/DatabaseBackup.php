<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DatabaseBackup extends Model
{
    protected $fillable = [
        'user_id',
        'backup_no',
        'database_name',
        'file_name',
        'file_path',
        'file_size',
        'drive_file_id',
        'drive_file_name',
        'drive_web_view_link',
        'drive_status',
        'drive_uploaded_at',
        'drive_error',
        'status',
        'notes',
    ];

    protected $casts = [
        'drive_uploaded_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::deleting(function (DatabaseBackup $databaseBackup): void {
            if ($databaseBackup->file_path && Storage::disk('local')->exists($databaseBackup->file_path)) {
                Storage::disk('local')->delete($databaseBackup->file_path);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = (int) $this->file_size;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        }

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' Bytes';
    }
}