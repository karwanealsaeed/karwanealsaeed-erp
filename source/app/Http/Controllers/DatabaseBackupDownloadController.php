<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\DatabaseBackup;
use Illuminate\Support\Facades\Storage;

class DatabaseBackupDownloadController extends Controller
{
    public function __invoke(DatabaseBackup $databaseBackup)
    {
        abort_unless(auth()->user()?->hasPermission('database_backups.download') ?? false, 403);

        abort_unless(Storage::disk('local')->exists($databaseBackup->file_path), 404);

        $user = auth()->user();

        ActivityLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'user_email' => $user?->email,

            'action' => 'Downloaded',
            'module' => 'Database Backup',
            'subject_type' => DatabaseBackup::class,
            'subject_id' => $databaseBackup->id,

            'record_title' => $databaseBackup->backup_no,
            'description' => 'Downloaded Database Backup: ' . $databaseBackup->backup_no,

            'old_values' => null,
            'new_values' => [
                'backup_no' => $databaseBackup->backup_no,
                'database_name' => $databaseBackup->database_name,
                'file_name' => $databaseBackup->file_name,
                'file_size' => $databaseBackup->formatted_file_size,
            ],

            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);

        return Storage::disk('local')->download(
            $databaseBackup->file_path,
            $databaseBackup->file_name
        );
    }
}