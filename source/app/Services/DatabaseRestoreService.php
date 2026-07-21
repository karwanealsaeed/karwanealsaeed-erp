<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\DatabaseBackup;
use App\Models\DatabaseRestoreLog;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class DatabaseRestoreService
{
    public function __construct(
        private readonly DatabaseBackupService $backupService,
        private readonly MysqlDatabaseRestoreExecutor $executor,
    ) {
    }

    public function inspect(
        DatabaseBackup $backup,
    ): array {
        $this->ensureFeatureEnabled();
        $this->ensureEnvironmentAllowed();
        $this->ensureMysqlConnection();

        $currentDatabase =
            $this->currentDatabase();

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
            str_replace(
                '\\',
                '/',
                trim(
                    (string) $backup->file_path,
                ),
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
            str_contains(
                $path,
                '../',
            )
        ) {
            throw new RuntimeException(
                'Unsafe backup path detected.',
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

        $disk =
            Storage::disk(
                'local',
            );

        if (! $disk->exists($path)) {
            throw new RuntimeException(
                'Local backup file missing hai.',
            );
        }

        $size =
            $disk->size(
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
            $disk->path(
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
            || ! is_file($mysqlBinary)
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

    public function execute(
        DatabaseBackup $backup,
        string $confirmation,
        ?int $requestedBy = null,
    ): array {
        $plan =
            $this->inspect(
                $backup,
            );

        $this->assertConfirmation(
            $backup,
            $confirmation,
        );

        $this->ensureExecutionEnabled();

        $lock =
            $this->acquireLock();

        $enteredMaintenance =
            false;

        $safetyBackup =
            null;

        $journalPath =
            null;

        $sourceSnapshot =
            $this->backupSnapshot(
                $backup,
            );

        $safetySnapshot =
            null;

        $importStarted =
            false;

        $rollbackFailed =
            false;

        $startedAt =
            now(
                'Asia/Karachi',
            );

        try {
            $safetyBackup =
                $this->backupService
                    ->create(
                        'Pre-Restore',
                        $requestedBy,
                    );

            if (
                ! $this->backupService
                    ->verify(
                        $safetyBackup,
                    )
            ) {
                throw new RuntimeException(
                    'Pre-restore safety backup verification fail hui.',
                );
            }

            $safetyBackup->refresh();

            $safetySnapshot =
                $this->backupSnapshot(
                    $safetyBackup,
                );

            $journalPath =
                $this->createJournal([
                    'status' =>
                        'Prepared',

                    'started_at' =>
                        $startedAt
                            ->toDateTimeString(),

                    'source_backup' =>
                        $sourceSnapshot,

                    'safety_backup' =>
                        $safetySnapshot,

                    'database_name' =>
                        $plan['database_name'],

                    'requested_by' =>
                        $requestedBy,
                ]);

            if (
                ! app()
                    ->isDownForMaintenance()
            ) {
                $exitCode =
                    Artisan::call(
                        'down',
                        [
                            '--retry' =>
                                (int) config(
                                    'database_restore.maintenance_retry_seconds',
                                    60,
                                ),
                        ],
                    );

                if ($exitCode !== 0) {
                    throw new RuntimeException(
                        'Application maintenance mode enable nahi hua.',
                    );
                }

                $enteredMaintenance =
                    true;
            }

            $this->updateJournal(
                $journalPath,
                [
                    'status' =>
                        'Importing',
                ],
            );

            $importStarted =
                true;

            $importResult =
                $this->executor
                    ->import(
                        $plan['absolute_path'],
                        $plan['database_name'],
                    );

            $this->executor
                ->reconnect();

            $migrationExitCode =
                Artisan::call(
                    'migrate',
                    [
                        '--force' =>
                            true,
                    ],
                );

            if ($migrationExitCode !== 0) {
                throw new RuntimeException(
                    'Post-restore migrations fail huin.',
                );
            }

            $this->executor
                ->reconnect();

            $validation =
                $this->executor
                    ->validate(
                        (array) config(
                            'database_restore.required_tables',
                            [],
                        ),
                    );

            $sourceRecord =
                $this->restoreBackupRecord(
                    $sourceSnapshot,
                );

            $safetyRecord =
                $this->restoreBackupRecord(
                    $safetySnapshot,
                );

            $restoreLog =
                $this->recordRestoreLog(
                    sourceBackup: $sourceRecord,
                    safetyBackup: $safetyRecord,
                    requestedBy: $requestedBy,
                    status: 'Completed',
                    rollbackStatus: 'Not Required',
                    startedAt: $startedAt,
                    completedAt: now('Asia/Karachi'),
                    errorMessage: null,
                    context: [
                        'import' =>
                            $importResult,

                        'validation' =>
                            $validation,

                        'journal_path' =>
                            $journalPath,
                    ],
                );

            $this->recordActivity(
                sourceBackup: $sourceRecord,
                restoreLog: $restoreLog,
                requestedBy: $requestedBy,
                action: 'Restored',
                description:
                    'Database restored successfully from '
                    .$sourceRecord->backup_no
                    .'. Safety backup: '
                    .$safetyRecord->backup_no,
            );

            $this->deleteJournal(
                $journalPath,
            );

            return [
                'status' =>
                    'Completed',

                'restore_log_id' =>
                    $restoreLog->id,

                'source_backup_no' =>
                    $sourceRecord->backup_no,

                'safety_backup_no' =>
                    $safetyRecord->backup_no,

                'database_name' =>
                    $plan['database_name'],

                'import' =>
                    $importResult,

                'validation' =>
                    $validation,
            ];
        } catch (Throwable $exception) {
            $rollbackStatus =
                'Not Required';

            $rollbackException =
                null;

            if (
                $importStarted
                && $safetyBackup
                instanceof DatabaseBackup
                && is_array($safetySnapshot)
            ) {
                try {
                    $rollbackStatus =
                        'Running';

                    $this->updateJournal(
                        $journalPath,
                        [
                            'status' =>
                                'Rolling Back',

                            'restore_error' =>
                                $exception->getMessage(),
                        ],
                    );

                    $safetyPath =
                        Storage::disk('local')
                            ->path(
                                (string) $safetyBackup
                                    ->file_path,
                            );

                    $this->executor
                        ->import(
                            $safetyPath,
                            $plan['database_name'],
                        );

                    $this->executor
                        ->reconnect();

                    $rollbackMigrationExit =
                        Artisan::call(
                            'migrate',
                            [
                                '--force' =>
                                    true,
                            ],
                        );

                    if (
                        $rollbackMigrationExit
                        !== 0
                    ) {
                        throw new RuntimeException(
                            'Rollback migrations fail huin.',
                        );
                    }

                    $this->executor
                        ->reconnect();

                    $rollbackValidation =
                        $this->executor
                            ->validate(
                                (array) config(
                                    'database_restore.required_tables',
                                    [],
                                ),
                            );

                    $sourceRecord =
                        $this->restoreBackupRecord(
                            $sourceSnapshot,
                        );

                    $safetyRecord =
                        $this->restoreBackupRecord(
                            $safetySnapshot,
                        );

                    $rollbackStatus =
                        'Completed';

                    $restoreLog =
                        $this->recordRestoreLog(
                            sourceBackup: $sourceRecord,
                            safetyBackup: $safetyRecord,
                            requestedBy: $requestedBy,
                            status: 'Failed',
                            rollbackStatus: $rollbackStatus,
                            startedAt: $startedAt,
                            completedAt: null,
                            errorMessage: $exception
                                ->getMessage(),
                            context: [
                                'rollback_validation' =>
                                    $rollbackValidation,

                                'journal_path' =>
                                    $journalPath,
                            ],
                        );

                    $this->recordActivity(
                        sourceBackup: $sourceRecord,
                        restoreLog: $restoreLog,
                        requestedBy: $requestedBy,
                        action: 'Restore Failed',
                        description:
                            'Database restore failed and automatic rollback completed. '
                            .$exception->getMessage(),
                    );

                    $this->updateJournal(
                        $journalPath,
                        [
                            'status' =>
                                'Failed - Rolled Back',

                            'rollback_status' =>
                                'Completed',
                        ],
                    );
                } catch (Throwable $caughtRollbackException) {
                    $rollbackStatus =
                        'Failed';

                    $rollbackFailed =
                        true;

                    $rollbackException =
                        $caughtRollbackException;

                    $this->writeEmergencyFailure(
                        sourceBackup: $sourceSnapshot,
                        safetyBackup: $safetySnapshot,
                        restoreException: $exception,
                        rollbackException: $caughtRollbackException,
                        journalPath: $journalPath,
                    );
                }
            } else {
                $this->recordFailureBeforeImport(
                    sourceSnapshot: $sourceSnapshot,
                    safetySnapshot: $safetySnapshot,
                    requestedBy: $requestedBy,
                    startedAt: $startedAt,
                    exception: $exception,
                    journalPath: $journalPath,
                );
            }

            if ($rollbackFailed) {
                throw new RuntimeException(
                    'Database restore fail hui aur automatic rollback bhi fail hua. '
                    .'Application maintenance mode mein rakhi gayi hai. '
                    .'Restore error: '
                    .$exception->getMessage()
                    .'; Rollback error: '
                    .$rollbackException
                        ?->getMessage(),
                    0,
                    $exception,
                );
            }

            throw new RuntimeException(
                'Database restore fail hui. Automatic rollback status: '
                .$rollbackStatus
                .'. Error: '
                .$exception->getMessage(),
                0,
                $exception,
            );
        } finally {
            if (
                $enteredMaintenance
                && ! $rollbackFailed
                && app()
                    ->isDownForMaintenance()
            ) {
                Artisan::call(
                    'up',
                );
            }

            $this->releaseLock(
                $lock,
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

    private function ensureExecutionEnabled(): void
    {
        if (
            ! config(
                'database_restore.execution_enabled',
                false,
            )
        ) {
            throw new RuntimeException(
                'Actual restore execution abhi locked hai. '
                .'DATABASE_RESTORE_EXECUTION_ENABLED=true required hai.',
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
            app()->environment(
                'testing',
            )
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

    private function currentDatabase(): string
    {
        $database =
            trim(
                (string) config(
                    'database.connections.'
                    .config('database.default')
                    .'.database',
                ),
            );

        if ($database === '') {
            throw new RuntimeException(
                'Current database name configured nahi hai.',
            );
        }

        return $database;
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
                        static fn (
                            string $name,
                        ): string =>
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

    private function acquireLock()
    {
        $path =
            Storage::disk('local')
                ->path(
                    (string) config(
                        'database_restore.lock_path',
                        'restore-locks/database-restore.lock',
                    ),
                );

        $directory =
            dirname(
                $path,
            );

        if (
            ! is_dir($directory)
            && ! mkdir(
                $directory,
                0775,
                true,
            )
            && ! is_dir($directory)
        ) {
            throw new RuntimeException(
                'Database restore lock directory create nahi hui.',
            );
        }

        $handle =
            fopen(
                $path,
                'c+',
            );

        if (! is_resource($handle)) {
            throw new RuntimeException(
                'Database restore lock file open nahi hui.',
            );
        }

        if (
            ! flock(
                $handle,
                LOCK_EX | LOCK_NB,
            )
        ) {
            fclose(
                $handle,
            );

            throw new RuntimeException(
                'Ek database restore operation already running hai.',
            );
        }

        ftruncate(
            $handle,
            0,
        );

        fwrite(
            $handle,
            json_encode([
                'pid' =>
                    getmypid(),

                'started_at' =>
                    now('Asia/Karachi')
                        ->toDateTimeString(),
            ]),
        );

        fflush(
            $handle,
        );

        return $handle;
    }

    private function releaseLock(
        mixed $handle,
    ): void {
        if (! is_resource($handle)) {
            return;
        }

        flock(
            $handle,
            LOCK_UN,
        );

        fclose(
            $handle,
        );
    }

    private function backupSnapshot(
        DatabaseBackup $backup,
    ): array {
        return array_intersect_key(
            $backup->getAttributes(),
            array_flip(
                $backup->getFillable(),
            ),
        );
    }

    private function restoreBackupRecord(
        array $snapshot,
    ): DatabaseBackup {
        $backupNo =
            (string) (
                $snapshot['backup_no']
                ?? ''
            );

        if ($backupNo === '') {
            throw new RuntimeException(
                'Backup snapshot number missing hai.',
            );
        }

        return DatabaseBackup::query()
            ->updateOrCreate(
                [
                    'backup_no' =>
                        $backupNo,
                ],
                $snapshot,
            );
    }

    private function safeRequestedBy(
        ?int $requestedBy,
    ): ?int {
        if (
            ! $requestedBy
            || ! Schema::hasTable(
                'users',
            )
        ) {
            return null;
        }

        return User::query()
            ->whereKey(
                $requestedBy,
            )
            ->exists()
                ? $requestedBy
                : null;
    }

    private function recordRestoreLog(
        DatabaseBackup $sourceBackup,
        DatabaseBackup $safetyBackup,
        ?int $requestedBy,
        string $status,
        string $rollbackStatus,
        mixed $startedAt,
        mixed $completedAt,
        ?string $errorMessage,
        array $context,
    ): DatabaseRestoreLog {
        return DatabaseRestoreLog::query()
            ->create([
                'database_backup_id' =>
                    $sourceBackup->id,

                'requested_by' =>
                    $this->safeRequestedBy(
                        $requestedBy,
                    ),

                'source_backup_no' =>
                    $sourceBackup->backup_no,

                'safety_backup_no' =>
                    $safetyBackup->backup_no,

                'database_name' =>
                    $this->currentDatabase(),

                'source_checksum' =>
                    $sourceBackup
                        ->checksum_sha256,

                'status' =>
                    $status,

                'rollback_status' =>
                    $rollbackStatus,

                'started_at' =>
                    $startedAt,

                'completed_at' =>
                    $completedAt,

                'failed_at' =>
                    $status === 'Failed'
                        ? now('Asia/Karachi')
                        : null,

                'error_message' =>
                    $errorMessage
                        ? mb_substr(
                            $errorMessage,
                            0,
                            60000,
                        )
                        : null,

                'context' =>
                    $context,
            ]);
    }

    private function recordActivity(
        DatabaseBackup $sourceBackup,
        DatabaseRestoreLog $restoreLog,
        ?int $requestedBy,
        string $action,
        string $description,
    ): void {
        $user =
            $this->safeRequestedBy(
                $requestedBy,
            )
                ? User::query()
                    ->find(
                        $requestedBy,
                    )
                : null;

        ActivityLog::query()
            ->create([
                'user_id' =>
                    $user?->id,

                'user_name' =>
                    $user?->name,

                'user_email' =>
                    $user?->email,

                'action' =>
                    $action,

                'module' =>
                    'Database Restore',

                'subject_type' =>
                    DatabaseRestoreLog::class,

                'subject_id' =>
                    $restoreLog->id,

                'record_title' =>
                    $sourceBackup->backup_no,

                'description' =>
                    $description,

                'old_values' =>
                    null,

                'new_values' => [
                    'source_backup_no' =>
                        $sourceBackup->backup_no,

                    'status' =>
                        $restoreLog->status,

                    'rollback_status' =>
                        $restoreLog
                            ->rollback_status,
                ],

                'ip_address' =>
                    request()?->ip(),

                'user_agent' =>
                    request()?->userAgent(),
            ]);
    }

    private function recordFailureBeforeImport(
        array $sourceSnapshot,
        ?array $safetySnapshot,
        ?int $requestedBy,
        mixed $startedAt,
        Throwable $exception,
        ?string $journalPath,
    ): void {
        try {
            $sourceBackup =
                $this->restoreBackupRecord(
                    $sourceSnapshot,
                );

            $safetyBackup =
                is_array($safetySnapshot)
                    ? $this->restoreBackupRecord(
                        $safetySnapshot,
                    )
                    : $sourceBackup;

            $restoreLog =
                $this->recordRestoreLog(
                    sourceBackup: $sourceBackup,
                    safetyBackup: $safetyBackup,
                    requestedBy: $requestedBy,
                    status: 'Failed',
                    rollbackStatus: 'Not Required',
                    startedAt: $startedAt,
                    completedAt: null,
                    errorMessage: $exception->getMessage(),
                    context: [
                        'journal_path' =>
                            $journalPath,
                    ],
                );

            $this->recordActivity(
                sourceBackup: $sourceBackup,
                restoreLog: $restoreLog,
                requestedBy: $requestedBy,
                action: 'Restore Failed',
                description:
                    'Database restore failed before import started. '
                    .$exception->getMessage(),
            );
        } catch (Throwable $loggingException) {
            report(
                $loggingException,
            );
        }
    }

    private function createJournal(
        array $data,
    ): string {
        $path =
            'restore-journals/'
            .Str::uuid()
            .'.json';

        $written =
            Storage::disk('local')
                ->put(
                    $path,
                    json_encode(
                        $data,
                        JSON_PRETTY_PRINT
                        | JSON_UNESCAPED_SLASHES
                        | JSON_UNESCAPED_UNICODE,
                    ),
                );

        if (! $written) {
            throw new RuntimeException(
                'Restore safety journal create nahi hua.',
            );
        }

        return $path;
    }

    private function updateJournal(
        ?string $path,
        array $changes,
    ): void {
        if (
            ! $path
            || ! Storage::disk('local')
                ->exists(
                    $path,
                )
        ) {
            return;
        }

        $existing =
            json_decode(
                Storage::disk('local')
                    ->get(
                        $path,
                    ),
                true,
            );

        if (! is_array($existing)) {
            $existing = [];
        }

        Storage::disk('local')
            ->put(
                $path,
                json_encode(
                    array_merge(
                        $existing,
                        $changes,
                        [
                            'updated_at' =>
                                now('Asia/Karachi')
                                    ->toDateTimeString(),
                        ],
                    ),
                    JSON_PRETTY_PRINT
                    | JSON_UNESCAPED_SLASHES
                    | JSON_UNESCAPED_UNICODE,
                ),
            );
    }

    private function deleteJournal(
        ?string $path,
    ): void {
        if (!$path) {
            return;
        }

        Storage::disk('local')
            ->delete(
                $path,
            );
    }

    private function writeEmergencyFailure(
        array $sourceBackup,
        ?array $safetyBackup,
        Throwable $restoreException,
        Throwable $rollbackException,
        ?string $journalPath,
    ): void {
        $payload = [
            'time' =>
                now('Asia/Karachi')
                    ->toDateTimeString(),

            'source_backup' =>
                $sourceBackup,

            'safety_backup' =>
                $safetyBackup,

            'restore_error' =>
                $restoreException
                    ->getMessage(),

            'rollback_error' =>
                $rollbackException
                    ->getMessage(),

            'journal_path' =>
                $journalPath,
        ];

        Storage::disk('local')
            ->put(
                'restore-journals/emergency-'
                .now('Asia/Karachi')
                    ->format(
                        'Ymd-His',
                    )
                .'.json',
                json_encode(
                    $payload,
                    JSON_PRETTY_PRINT
                    | JSON_UNESCAPED_SLASHES
                    | JSON_UNESCAPED_UNICODE,
                ),
            );

        logger()->critical(
            'Database restore and automatic rollback both failed.',
            $payload,
        );
    }
}