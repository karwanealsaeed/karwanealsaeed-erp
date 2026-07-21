<?php

namespace Tests\Feature;

use App\Models\DatabaseBackup;
use App\Services\DatabaseRestoreService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class DatabaseRestoreSafetyTest extends TestCase
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
                false,

            'database_restore.allow_testing_non_mysql' =>
                true,

            'database_restore.mysql_binary' =>
                __FILE__,

            'database_restore.allowed_environments' => [
                'testing',
            ],
        ]);
    }

    public function test_restore_log_table_exists(): void
    {
        $this->assertTrue(
            Schema::hasTable(
                'database_restore_logs',
            ),
        );

        foreach (
            [
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
            ]
            as $column
        ) {
            $this->assertTrue(
                Schema::hasColumn(
                    'database_restore_logs',
                    $column,
                ),
                'Missing column: '.$column,
            );
        }
    }

    public function test_verified_matching_backup_passes_restore_inspection(): void
    {
        $backup =
            $this->verifiedBackup();

        $plan =
            app(
                DatabaseRestoreService::class,
            )->inspect(
                $backup,
            );

        $this->assertTrue(
            $plan['ready'],
        );

        $this->assertSame(
            'BK-90001',
            $plan['backup_no'],
        );

        $this->assertSame(
            ':memory:',
            $plan['database_name'],
        );

        $this->assertFalse(
            $plan['execution_enabled'],
        );
    }

    public function test_backup_for_another_database_is_rejected(): void
    {
        $backup =
            $this->verifiedBackup([
                'database_name' =>
                    'another_database',
            ]);

        $this->expectException(
            RuntimeException::class,
        );

        $this->expectExceptionMessage(
            'current database se match nahi karti',
        );

        app(
            DatabaseRestoreService::class,
        )->inspect(
            $backup,
        );
    }

    public function test_unsafe_database_statement_is_rejected(): void
    {
        $backup =
            $this->verifiedBackup(
                [],
                $this->validSql()
                ."\nDROP DATABASE unsafe_database;\n",
            );

        $this->expectException(
            RuntimeException::class,
        );

        $this->expectExceptionMessage(
            'Unsafe SQL statement detected',
        );

        app(
            DatabaseRestoreService::class,
        )->inspect(
            $backup,
        );
    }

    public function test_wrong_use_database_statement_is_rejected(): void
    {
        $backup =
            $this->verifiedBackup(
                [],
                "USE `wrong_database`;\n"
                .$this->validSql(),
            );

        $this->expectException(
            RuntimeException::class,
        );

        $this->expectExceptionMessage(
            'doosri database ko target karti hai',
        );

        app(
            DatabaseRestoreService::class,
        )->inspect(
            $backup,
        );
    }

    public function test_exact_backup_confirmation_is_required(): void
    {
        $backup =
            $this->verifiedBackup();

        $this->expectException(
            RuntimeException::class,
        );

        $this->expectExceptionMessage(
            'Exact backup number confirmation required',
        );

        app(
            DatabaseRestoreService::class,
        )->assertConfirmation(
            $backup,
            'WRONG',
        );
    }

    public function test_restore_command_dry_run_passes_without_changing_database(): void
    {
        $backup =
            $this->verifiedBackup();

        $this->artisan(
            'database:restore',
            [
                'backup' =>
                    $backup->backup_no,

                '--dry-run' =>
                    true,
            ],
        )
            ->expectsOutputToContain(
                'Restore dry-run passed',
            )
            ->assertSuccessful();

        $this->assertDatabaseCount(
            'database_restore_logs',
            0,
        );
    }

    public function test_actual_restore_remains_locked_in_phase_one(): void
    {
        $backup =
            $this->verifiedBackup();

        $this->artisan(
            'database:restore',
            [
                'backup' =>
                    $backup->backup_no,

                '--confirm' =>
                    $backup->backup_no,
            ],
        )
            ->expectsOutputToContain(
                'Actual restore execution abhi locked hai',
            )
            ->assertFailed();
    }

    public function test_database_backup_table_contains_restore_readiness_action(): void
    {
        $contents =
            file_get_contents(
                app_path(
                    'Filament/Resources/DatabaseBackups/Tables/DatabaseBackupsTable.php',
                ),
            );

        $this->assertStringContainsString(
            "Action::make('restore_readiness')",
            $contents,
        );

        $this->assertStringContainsString(
            'database_backups.restore',
            $contents,
        );

        $this->assertStringContainsString(
            'DatabaseRestoreService::class',
            $contents,
        );
    }

    public function test_backup_service_accepts_pre_restore_backup_type(): void
    {
        $contents =
            file_get_contents(
                app_path(
                    'Services/DatabaseBackupService.php',
                ),
            );

        $this->assertStringContainsString(
            "'Pre-Restore'",
            $contents,
        );
    }

    private function verifiedBackup(
        array $overrides = [],
        ?string $contents = null,
    ): DatabaseBackup {
        $contents ??=
            $this->validSql();

        $filePath =
            'backups/BK-90001.sql';

        Storage::disk('local')
            ->put(
                $filePath,
                $contents,
            );

        return DatabaseBackup::query()
            ->create(
                array_merge(
                    [
                        'backup_no' =>
                            'BK-90001',

                        'database_name' =>
                            ':memory:',

                        'file_name' =>
                            'BK-90001.sql',

                        'file_path' =>
                            $filePath,

                        'file_size' =>
                            strlen(
                                $contents,
                            ),

                        'backup_type' =>
                            'Manual',

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
                    ],
                    $overrides,
                ),
            );
    }

    private function validSql(): string
    {
        return implode(
            "\n",
            [
                '-- Karwan E Al Saeed Travel & Tours Database Backup',
                '-- Database: SQLite in-memory testing',
                '',
                'SET FOREIGN_KEY_CHECKS=0;',
                'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";',
                'START TRANSACTION;',
                '',
                'DROP TABLE IF EXISTS `users`;',
                'CREATE TABLE `users` (`id` bigint unsigned NOT NULL);',
                'INSERT INTO `users` (`id`) VALUES (1);',
                str_repeat(
                    '-- restore safety filler ',
                    20,
                ),
                'COMMIT;',
                'SET FOREIGN_KEY_CHECKS=1;',
                '',
            ],
        );
    }
}