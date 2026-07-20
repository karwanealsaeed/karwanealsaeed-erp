<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\DatabaseBackupService;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Throwable;

class DailyBackupScheduler extends Command
{
    protected $signature =
        'kes:daily-backup
        {--retention-days= : Days to keep old backups}
        {--minimum-backups= : Minimum recent backups to retain}';

    protected $description =
        'Create, verify and retain daily ERP database backups';

    public function handle(
        DatabaseBackupService $backupService,
    ): int {
        $this->info(
            'Starting verified daily backup...',
        );

        try {
            $backup =
                $backupService->create(
                    'Scheduled',
                );

            $deleted =
                $backupService->prune(
                    $this->retentionDays(),
                    $this->minimumBackups(),
                );

            $this->runMaintenance();

            $this->info(
                'Backup created: '
                .$backup->backup_no,
            );

            $this->line(
                'Storage path: '
                .$backup->file_path,
            );

            $this->line(
                'Integrity: '
                .$backup->verification_status,
            );

            $this->line(
                'SHA-256: '
                .$backup->checksum_sha256,
            );

            $this->line(
                'Old backups pruned: '
                .$deleted,
            );

            $this->info(
                'Daily backup completed successfully.',
            );

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);

            $this->error(
                'Database backup failed: '
                .$exception->getMessage(),
            );

            $this->notifyFailure(
                $exception,
            );

            return self::FAILURE;
        }
    }

    private function retentionDays(): int
    {
        $option =
            $this->option(
                'retention-days',
            );

        if (is_numeric($option)) {
            return max(
                1,
                (int) $option,
            );
        }

        return max(
            1,
            (int) config(
                'backup.retention_days',
                30,
            ),
        );
    }

    private function minimumBackups(): int
    {
        $option =
            $this->option(
                'minimum-backups',
            );

        if (is_numeric($option)) {
            return max(
                1,
                (int) $option,
            );
        }

        return max(
            1,
            (int) config(
                'backup.minimum_backups',
                7,
            ),
        );
    }

    private function runMaintenance(): void
    {
        try {
            Artisan::call(
                'queue:prune-failed',
                [
                    '--hours' => 168,
                ],
            );
        } catch (Throwable $exception) {
            report($exception);
        }

        try {
            Artisan::call(
                'cache:prune-stale-tags',
            );
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function notifyFailure(
        Throwable $exception,
    ): void {
        $recipients =
            User::query()
                ->where(
                    'is_active',
                    true,
                )
                ->get()
                ->filter(
                    fn (User $user): bool =>
                        $user->isAdmin()
                        || $user->hasPermission(
                            'database_backups.view',
                        )
                        || $user->hasPermission(
                            'database_backups.create',
                        ),
                )
                ->values();

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::make()
            ->title(
                'Scheduled database backup failed',
            )
            ->body(
                mb_substr(
                    $exception->getMessage(),
                    0,
                    500,
                ),
            )
            ->danger()
            ->sendToDatabase(
                $recipients,
            );
    }
}