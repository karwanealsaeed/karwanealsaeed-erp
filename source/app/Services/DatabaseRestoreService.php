<?php

namespace App\Services;

use App\Models\DatabaseBackup;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DatabaseRestoreService
{
    public function __construct(
        private readonly DatabaseBackupService $backupService,
    ) {
    }

    public function inspect(
        DatabaseBackup $backup,
    ): array {
        $this->ensureFeatureEnabled();
        $this->ensureEnvironmentAllowed();
        $this->ensureMysqlConnection();

        $currentDatabase =
            trim(
                (string) config(
                    'database.connections.'
                    .config('database.default')
                    .'.database',
                ),
            );

        if ($currentDatabase === '') {
            throw new RuntimeException(
                'Current database name configured nahi hai.',
            );
        }

        if (
            trim(
                (string) $backup->database_name,
            )
            !== $currentDatabase
        ) {
            throw new RuntimeException(
                'Backup database current database se match nahi karti. '
                .'Backup: '
                .($backup->database_name ?: '-')
                .', Current: '
                .$currentDatabase,
            );
        }

        if (
            $backup->status
            !== 'Completed'
        ) {
            throw new RuntimeException(
                'Sirf completed backup restore ho sakti hai.',
            );
        }

        if (
            ! $this->backupService
                ->verify(
                    $backup,
                )
        ) {
            throw new RuntimeException(
                'Backup integrity verification fail hui.',
            );
        }

        $backup->refresh();

        if (
            $backup->verification_status
            !== 'Verified'
        ) {
            throw new RuntimeException(
                'Backup verified state mein nahi hai.',
            );
        }

        $path =
            trim(
                (string) $backup->file_path,
            );

        if (
            ! str_starts_with(
                $path,
                'backups/',
            )
        ) {
            throw new RuntimeException(
                'Backup path private tracked directory mein nahi hai.',
            );
        }

        if (
            basename($path)
            !== (string) $backup->file_name
        ) {
            throw new RuntimeException(
                'Backup path aur file name match nahi karte.',
            );
        }

        if (
            strtolower(
                pathinfo(
                    $path,
                    PATHINFO_EXTENSION,
                ),
            )
            !== 'sql'
        ) {
            throw new RuntimeException(
                'Sirf SQL backup restore ho sakti hai.',
            );
        }

        if (
            ! Storage::disk('local')
                ->exists(
                    $path,
                )
        ) {
            throw new RuntimeException(
                'Local backup file missing hai.',
            );
        }

        $size =
            Storage::disk('local')
                ->size(
                    $path,
                );

        $maximumSize =
            (int) config(
                'database_restore.max_file_bytes',
                1073741824,
            );

        if (
            $size <= 0
            || $size > $maximumSize
        ) {
            throw new RuntimeException(
                'Backup file size restore limit ke andar nahi hai.',
            );
        }

        $absolutePath =
            Storage::disk('local')
                ->path(
                    $path,
                );

        $contents =
            file_get_contents(
                $absolutePath,
            );

        if (
            ! is_string($contents)
            || $contents === ''
        ) {
            throw new RuntimeException(
                'Backup SQL read nahi hui.',
            );
        }

        foreach (
            (array) config(
                'database_restore.required_markers',
                [],
            )
            as $marker
        ) {
            if (
                ! str_contains(
                    $contents,
                    (string) $marker,
                )
            ) {
                throw new RuntimeException(
                    'Required SQL marker missing: '
                    .$marker,
                );
            }
        }

        foreach (
            (array) config(
                'database_restore.unsafe_patterns',
                [],
            )
            as $pattern
        ) {
            if (
                preg_match(
                    (string) $pattern,
                    $contents,
                )
                === 1
            ) {
                throw new RuntimeException(
                    'Unsafe SQL statement detected: '
                    .$pattern,
                );
            }
        }

        $this->validateUseStatements(
            $contents,
            $currentDatabase,
        );

        $mysqlBinary =
            trim(
                (string) config(
                    'database_restore.mysql_binary',
                ),
            );

        if (
            $mysqlBinary === ''
            || ! is_file(
                $mysqlBinary,
            )
        ) {
            throw new RuntimeException(
                'Configured mysql client executable nahi mili.',
            );
        }

        return [
            'ready' =>
                true,

            'backup_id' =>
                $backup->id,

            'backup_no' =>
                $backup->backup_no,

            'database_name' =>
                $currentDatabase,

            'file_name' =>
                $backup->file_name,

            'file_path' =>
                $path,

            'absolute_path' =>
                $absolutePath,

            'file_size' =>
                $size,

            'formatted_file_size' =>
                $backup
                    ->formatted_file_size,

            'checksum_sha256' =>
                $backup
                    ->checksum_sha256,

            'verification_status' =>
                $backup
                    ->verification_status,

            'mysql_binary' =>
                $mysqlBinary,

            'execution_enabled' =>
                (bool) config(
                    'database_restore.execution_enabled',
                    false,
                ),

            'requires_safety_backup' =>
                true,

            'requires_exact_confirmation' =>
                $backup->backup_no,
        ];
    }

    public function assertConfirmation(
        DatabaseBackup $backup,
        string $confirmation,
    ): void {
        if (
            trim($confirmation)
            !== (string) $backup->backup_no
        ) {
            throw new RuntimeException(
                'Exact backup number confirmation required hai: '
                .$backup->backup_no,
            );
        }
    }

    private function ensureFeatureEnabled(): void
    {
        if (
            ! config(
                'database_restore.enabled',
                true,
            )
        ) {
            throw new RuntimeException(
                'Database restore feature disabled hai.',
            );
        }
    }

    private function ensureEnvironmentAllowed(): void
    {
        $allowed =
            (array) config(
                'database_restore.allowed_environments',
                [],
            );

        if (
            ! in_array(
                app()->environment(),
                $allowed,
                true,
            )
        ) {
            throw new RuntimeException(
                'Database restore current environment mein allowed nahi hai.',
            );
        }
    }

    private function ensureMysqlConnection(): void
    {
        if (
            app()->environment('testing')
            && config(
                'database_restore.allow_testing_non_mysql',
                false,
            )
        ) {
            return;
        }

        $connection =
            config(
                'database.default',
            );

        $driver =
            config(
                'database.connections.'
                .$connection
                .'.driver',
            );

        if ($driver !== 'mysql') {
            throw new RuntimeException(
                'Database restore sirf MySQL connection ke liye available hai.',
            );
        }
    }

    private function validateUseStatements(
        string $contents,
        string $currentDatabase,
    ): void {
        preg_match_all(
            '/^\s*USE\s+[`"]?([^`";\s]+)[`"]?\s*;/mi',
            $contents,
            $matches,
        );

        $databaseNames =
            array_values(
                array_unique(
                    array_map(
                        fn (string $name): string =>
                            trim(
                                $name,
                            ),
                        $matches[1]
                        ?? [],
                    ),
                ),
            );

        foreach ($databaseNames as $databaseName) {
            if ($databaseName !== $currentDatabase) {
                throw new RuntimeException(
                    'SQL USE statement doosri database ko target karti hai: '
                    .$databaseName,
                );
            }
        }
    }
}