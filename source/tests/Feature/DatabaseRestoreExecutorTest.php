<?php

namespace Tests\Feature;

use App\Models\DatabaseBackup;
use App\Models\DatabaseRestoreLog;
use App\Services\DatabaseBackupService;
use App\Services\DatabaseRestoreService;
use App\Services\MysqlDatabaseRestoreExecutor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class DatabaseRestoreExecutorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(
            'local',
        );

        config([
            'database_restore.enabled' =>
                true,

            'database_restore.execution_enabled' =>
                true,

            'database_restore.allow_testing_non_mysql' =>
                true,

            'database_restore.mysql_binary' =>
                __FILE__,

            'database_restore.allowed_environments' => [
                'testing',
            ],

            'database_restore.required_tables' => [
                'users',
                'migrations',
                'database_backups',
                'database_restore_logs',
                'activity_logs',
            ],

            'database_restore.lock_path' =>
                'restore-locks/database-restore.lock',
        ]);
    }

    protected function tearDown(): void
    {
        Artisan::call(
            'up',
        );

        Mockery::close();

        parent::tearDown();
    }

    public function test_execution_remains_blocked_when_configuration_is_disabled(): void
    {
        config([
            'database_restore.execution_enabled' =>
                false,
        ]);

        $source =
            $this->backup(
                'BK-91001',
            );

        $backupService =
            Mockery::mock(
                DatabaseBackupService::class,
            );

        $backupService
            ->shouldReceive(
                'verify',
            )
            ->andReturnTrue();

        $executor =
            Mockery::mock(
                MysqlDatabaseRestoreExecutor::class,
            );

        $service =
            new DatabaseRestoreService(
                $backupService,
                $executor,
            );

        $this->expectException(
            RuntimeException::class,
        );

        $this->expectExceptionMessage(
            'DATABASE_RESTORE_EXECUTION_ENABLED=true',
        );

        $service->execute(
            $source,
            $source->backup_no,
        );
    }

    public function test_successful_execution_creates_safety_backup_and_audit_log(): void
    {
        $source =
            $this->backup(
                'BK-91002',
            );

        $safety =
            $this->backup(
                'BK-91003',
                'Pre-Restore',
            );

        $backupService =
            Mockery::mock(
                DatabaseBackupService::class,
            );

        $backupService
            ->shouldReceive(
                'verify',
            )
            ->andReturnTrue();

        $backupService
            ->shouldReceive(
                'create',
            )
            ->once()
            ->with(
                'Pre-Restore',
                null,
            )
            ->andReturn(
                $safety,
            );

        $executor =
            Mockery::mock(
                MysqlDatabaseRestoreExecutor::class,
            );

        $executor
            ->shouldReceive(
                'import',
            )
            ->once()
            ->andReturn([
                'exit_code' =>
                    0,

                'duration_seconds' =>
                    0.1,

                'output' =>
                    '',
            ]);

        $executor
            ->shouldReceive(
                'reconnect',
            )
            ->twice();

        $executor
            ->shouldReceive(
                'validate',
            )
            ->once()
            ->andReturn([
                'required_tables' =>
                    config(
                        'database_restore.required_tables',
                    ),

                'missing_tables' =>
                    [],

                'migration_count' =>
                    1,
            ]);

        $service =
            new DatabaseRestoreService(
                $backupService,
                $executor,
            );

        $result =
            $service->execute(
                $source,
                $source->backup_no,
            );

        $this->assertSame(
            'Completed',
            $result['status'],
        );

        $this->assertSame(
            $source->backup_no,
            $result['source_backup_no'],
        );

        $this->assertSame(
            $safety->backup_no,
            $result['safety_backup_no'],
        );

        $this->assertDatabaseHas(
            'database_restore_logs',
            [
                'source_backup_no' =>
                    $source->backup_no,

                'safety_backup_no' =>
                    $safety->backup_no,

                'status' =>
                    'Completed',

                'rollback_status' =>
                    'Not Required',
            ],
        );

        $this->assertDatabaseHas(
            'activity_logs',
            [
                'module' =>
                    'Database Restore',

                'action' =>
                    'Restored',
            ],
        );

        $this->assertFalse(
            app()->isDownForMaintenance(),
        );
    }

    public function test_failed_import_runs_automatic_rollback(): void
    {
        $source =
            $this->backup(
                'BK-91004',
            );

        $safety =
            $this->backup(
                'BK-91005',
                'Pre-Restore',
            );

        $backupService =
            Mockery::mock(
                DatabaseBackupService::class,
            );

        $backupService
            ->shouldReceive(
                'verify',
            )
            ->andReturnTrue();

        $backupService
            ->shouldReceive(
                'create',
            )
            ->once()
            ->andReturn(
                $safety,
            );

        $executor =
            Mockery::mock(
                MysqlDatabaseRestoreExecutor::class,
            );

        $importCalls =
            0;

        $executor
            ->shouldReceive(
                'import',
            )
            ->twice()
            ->andReturnUsing(
                function () use (
                    &$importCalls,
                ): array {
                    $importCalls++;

                    if ($importCalls === 1) {
                        throw new RuntimeException(
                            'Simulated restore import failure.',
                        );
                    }

                    return [
                        'exit_code' =>
                            0,

                        'duration_seconds' =>
                            0.1,

                        'output' =>
                            '',
                    ];
                },
            );

        $executor
            ->shouldReceive(
                'reconnect',
            )
            ->twice();

        $executor
            ->shouldReceive(
                'validate',
            )
            ->once()
            ->andReturn([
                'required_tables' =>
                    config(
                        'database_restore.required_tables',
                    ),

                'missing_tables' =>
                    [],

                'migration_count' =>
                    1,
            ]);

        $service =
            new DatabaseRestoreService(
                $backupService,
                $executor,
            );

        try {
            $service->execute(
                $source,
                $source->backup_no,
            );

            $this->fail(
                'Restore failure expected thi.',
            );
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString(
                'Automatic rollback status: Completed',
                $exception->getMessage(),
            );
        }

        $this->assertDatabaseHas(
            'database_restore_logs',
            [
                'source_backup_no' =>
                    $source->backup_no,

                'status' =>
                    'Failed',

                'rollback_status' =>
                    'Completed',
            ],
        );

        $this->assertFalse(
            app()->isDownForMaintenance(),
        );
    }

    public function test_filesystem_lock_blocks_second_restore(): void
    {
        $source =
            $this->backup(
                'BK-91006',
            );

        $backupService =
            Mockery::mock(
                DatabaseBackupService::class,
            );

        $backupService
            ->shouldReceive(
                'verify',
            )
            ->andReturnTrue();

        $executor =
            Mockery::mock(
                MysqlDatabaseRestoreExecutor::class,
            );

        $lockPath =
            Storage::disk('local')
                ->path(
                    'restore-locks/database-restore.lock',
                );

        $directory =
            dirname(
                $lockPath,
            );

        if (! is_dir($directory)) {
            mkdir(
                $directory,
                0775,
                true,
            );
        }

        $handle =
            fopen(
                $lockPath,
                'c+',
            );

        $this->assertIsResource(
            $handle,
        );

        flock(
            $handle,
            LOCK_EX | LOCK_NB,
        );

        $service =
            new DatabaseRestoreService(
                $backupService,
                $executor,
            );

        try {
            $service->execute(
                $source,
                $source->backup_no,
            );

            $this->fail(
                'Concurrent restore lock expected tha.',
            );
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString(
                'already running',
                $exception->getMessage(),
            );
        } finally {
            flock(
                $handle,
                LOCK_UN,
            );

            fclose(
                $handle,
            );
        }
    }

    public function test_executor_source_uses_password_environment_and_no_shell_redirect(): void
    {
        $contents =
            file_get_contents(
                app_path(
                    'Services/MysqlDatabaseRestoreExecutor.php',
                ),
            );

        $this->assertStringContainsString(
            "'MYSQL_PWD'",
            $contents,
        );

        $this->assertStringContainsString(
            'new Process(',
            $contents,
        );

        $this->assertStringNotContainsString(
            'shell_exec',
            $contents,
        );

        $this->assertStringNotContainsString(
            ' --password=',
            $contents,
        );
    }

    public function test_restore_permission_is_available_in_user_form(): void
    {
        $contents =
            file_get_contents(
                app_path(
                    'Filament/Resources/Users/Schemas/UserForm.php',
                ),
            );

        $this->assertStringContainsString(
            "'database_backups_restore'",
            $contents,
        );

        $this->assertStringContainsString(
            'Database Backups - Restore',
            $contents,
        );
    }

    private function backup(
        string $backupNo,
        string $backupType = 'Manual',
    ): DatabaseBackup {
        $contents =
            $this->validSql();

        $fileName =
            $backupNo
            .'.sql';

        $filePath =
            'backups/'
            .$fileName;

        Storage::disk('local')
            ->put(
                $filePath,
                $contents,
            );

        return DatabaseBackup::query()
            ->create([
                'backup_no' =>
                    $backupNo,

                'database_name' =>
                    ':memory:',

                'file_name' =>
                    $fileName,

                'file_path' =>
                    $filePath,

                'file_size' =>
                    strlen(
                        $contents,
                    ),

                'backup_type' =>
                    $backupType,

                'checksum_sha256' =>
                    hash(
                        'sha256',
                        $contents,
                    ),

                'verification_status' =>
                    'Verified',

                'verified_at' =>
                    now(),

                'drive_status' =>
                    'Disabled',

                'status' =>
                    'Completed',
            ]);
    }

    private function validSql(): string
    {
        return implode(
            "\n",
            [
                '-- Karwan E Al Saeed Travel & Tours Database Backup',
                'SET FOREIGN_KEY_CHECKS=0;',
                'START TRANSACTION;',
                'DROP TABLE IF EXISTS `users`;',
                'CREATE TABLE `users` (`id` bigint unsigned NOT NULL);',
                'INSERT INTO `users` (`id`) VALUES (1);',
                str_repeat(
                    '-- restore executor filler ',
                    20,
                ),
                'COMMIT;',
                'SET FOREIGN_KEY_CHECKS=1;',
            ],
        );
    }
}