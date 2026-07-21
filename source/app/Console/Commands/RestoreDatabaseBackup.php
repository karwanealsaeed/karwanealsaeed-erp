<?php

namespace App\Console\Commands;

use App\Models\DatabaseBackup;
use App\Services\DatabaseRestoreService;
use Illuminate\Console\Command;
use RuntimeException;
use Throwable;

class RestoreDatabaseBackup extends Command
{
    protected $signature =
        'database:restore
        {backup : Backup database ID or backup number}
        {--dry-run : Validate restore readiness without changing data}
        {--confirm= : Exact backup number confirmation}';

    protected $description =
        'Validate or restore a verified ERP database backup';

    public function handle(
        DatabaseRestoreService $restoreService,
    ): int {
        try {
            $backup =
                $this->resolveBackup(
                    (string) $this->argument(
                        'backup',
                    ),
                );

            $plan =
                $restoreService->inspect(
                    $backup,
                );

            $this->table(
                [
                    'Check',
                    'Value',
                ],
                [
                    [
                        'Backup',
                        $plan['backup_no'],
                    ],
                    [
                        'Database',
                        $plan['database_name'],
                    ],
                    [
                        'File',
                        $plan['file_name'],
                    ],
                    [
                        'Size',
                        $plan[
                            'formatted_file_size'
                        ],
                    ],
                    [
                        'Verification',
                        $plan[
                            'verification_status'
                        ],
                    ],
                    [
                        'Checksum',
                        $plan[
                            'checksum_sha256'
                        ],
                    ],
                    [
                        'MySQL Client',
                        $plan[
                            'mysql_binary'
                        ],
                    ],
                    [
                        'Safety Backup',
                        'Required',
                    ],
                    [
                        'Execution',
                        $plan[
                            'execution_enabled'
                        ]
                            ? 'Enabled'
                            : 'Locked',
                    ],
                ],
            );

            if (
                (bool) $this->option(
                    'dry-run',
                )
            ) {
                $this->info(
                    'Restore dry-run passed. Database mein koi change nahi hua.',
                );

                return self::SUCCESS;
            }

            $restoreService
                ->assertConfirmation(
                    $backup,
                    (string) $this->option(
                        'confirm',
                    ),
                );

            if (
                ! config(
                    'database_restore.execution_enabled',
                    false,
                )
            ) {
                throw new RuntimeException(
                    'Actual restore execution abhi locked hai. '
                    .'Isolated restore drill pass hone ke baad enable hogi.',
                );
            }

            throw new RuntimeException(
                'Restore executor Phase 2 mein activate hoga.',
            );
        } catch (Throwable $exception) {
            $this->error(
                $exception->getMessage(),
            );

            return self::FAILURE;
        }
    }

    private function resolveBackup(
        string $identifier,
    ): DatabaseBackup {
        $query =
            DatabaseBackup::query();

        if (ctype_digit($identifier)) {
            $backup =
                $query->find(
                    (int) $identifier,
                );
        } else {
            $backup =
                $query->where(
                    'backup_no',
                    $identifier,
                )->first();
        }

        if (! $backup) {
            throw new RuntimeException(
                'Database backup record nahi mili.',
            );
        }

        return $backup;
    }
}