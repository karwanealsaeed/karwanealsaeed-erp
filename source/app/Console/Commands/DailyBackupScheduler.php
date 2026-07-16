<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class DailyBackupScheduler extends Command
{
    protected $signature = 'kes:daily-backup';

    protected $description = 'Run daily ERP backup tasks';

    public function handle(): int
    {
        $this->info('Starting daily backup...');

        $backupDir = storage_path('app/backups');

        if (! File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $fileName = 'backup-' . now('Asia/Karachi')->format('Y-m-d-H-i-s') . '.sql';
        $filePath = $backupDir . DIRECTORY_SEPARATOR . $fileName;

        $dbHost = config('database.connections.mysql.host');
        $dbPort = config('database.connections.mysql.port');
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');

        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s %s > "%s"',
            escapeshellarg($dbHost),
            escapeshellarg($dbPort),
            escapeshellarg($dbUser),
            escapeshellarg($dbPass),
            escapeshellarg($dbName),
            $filePath
        );

        exec($command, $output, $resultCode);

        if ($resultCode !== 0 || ! File::exists($filePath)) {
            $this->error('Database backup failed. Make sure mysqldump is available on server.');
            return self::FAILURE;
        }

        $this->info('Backup created: ' . $filePath);

        Artisan::call('queue:prune-failed', [
            '--hours' => 168,
        ]);

        Artisan::call('cache:prune-stale-tags');

        $this->info('Daily backup completed successfully.');

        return self::SUCCESS;
    }
}