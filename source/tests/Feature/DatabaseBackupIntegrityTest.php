<?php

namespace Tests\Feature;

use App\Models\DatabaseBackup;
use App\Models\User;
use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use RuntimeException;
use Tests\TestCase;

class DatabaseBackupIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_backup_integrity_columns_exist(): void
    {
        $columns = [
            'backup_type',
            'checksum_sha256',
            'verification_status',
            'verified_at',
            'verification_error',
        ];

        foreach ($columns as $column) {
            $this->assertTrue(
                Schema::hasColumn(
                    'database_backups',
                    $column,
                ),
                'Missing column: '.$column,
            );
        }
    }

    public function test_valid_backup_is_verified_and_hashed(): void
    {
        Storage::fake('local');

        $contents =
            $this->validSql();

        Storage::disk('local')
            ->put(
                'backups/valid.sql',
                $contents,
            );

        $backup =
            $this->backup([
                'file_name' =>
                    'valid.sql',

                'file_path' =>
                    'backups/valid.sql',

                'file_size' =>
                    strlen($contents),
            ]);

        $verified =
            app(
                DatabaseBackupService::class,
            )->verify($backup);

        $backup->refresh();

        $this->assertTrue($verified);

        $this->assertSame(
            'Verified',
            $backup->verification_status,
        );

        $this->assertSame(
            hash(
                'sha256',
                $contents,
            ),
            $backup->checksum_sha256,
        );

        $this->assertNotNull(
            $backup->verified_at,
        );

        $this->assertSame(
            'Completed',
            $backup->status,
        );
    }

    public function test_modified_backup_is_marked_corrupt(): void
    {
        Storage::fake('local');

        $contents =
            $this->validSql();

        Storage::disk('local')
            ->put(
                'backups/tampered.sql',
                $contents,
            );

        $backup =
            $this->backup([
                'file_name' =>
                    'tampered.sql',

                'file_path' =>
                    'backups/tampered.sql',

                'file_size' =>
                    strlen($contents),
            ]);

        $service =
            app(
                DatabaseBackupService::class,
            );

        $this->assertTrue(
            $service->verify($backup),
        );

        Storage::disk('local')
            ->put(
                'backups/tampered.sql',
                $contents
                ."\n-- modified",
            );

        $this->assertFalse(
            $service->verify(
                $backup->fresh(),
            ),
        );

        $backup->refresh();

        $this->assertSame(
            'Corrupt',
            $backup->verification_status,
        );

        $this->assertSame(
            'Failed',
            $backup->status,
        );

        $this->assertStringContainsString(
            'mismatch',
            strtolower(
                (string)
                $backup
                    ->verification_error,
            ),
        );
    }

    public function test_retention_preserves_minimum_recent_backups(): void
    {
        Storage::fake('local');

        $paths = [];

        for ($number = 1; $number <= 10; $number++) {
            $fileName =
                'retention-'
                .$number
                .'.sql';

            $filePath =
                'backups/'
                .$fileName;

            $paths[] =
                $filePath;

            $contents =
                $this->validSql();

            Storage::disk('local')
                ->put(
                    $filePath,
                    $contents,
                );

            $backup =
                $this->backup([
                    'backup_no' =>
                        'RT-'
                        .str_pad(
                            (string) $number,
                            5,
                            '0',
                            STR_PAD_LEFT,
                        ),

                    'file_name' =>
                        $fileName,

                    'file_path' =>
                        $filePath,

                    'file_size' =>
                        strlen($contents),
                ]);

            $backup->forceFill([
                'created_at' =>
                    now()->subDays(60),

                'updated_at' =>
                    now()->subDays(60),
            ])->saveQuietly();
        }

        $deleted =
            app(
                DatabaseBackupService::class,
            )->prune(
                30,
                7,
            );

        $this->assertSame(
            3,
            $deleted,
        );

        $this->assertDatabaseCount(
            'database_backups',
            7,
        );

        $this->assertFalse(
            Storage::disk('local')
                ->exists(
                    $paths[0],
                ),
        );

        $this->assertTrue(
            Storage::disk('local')
                ->exists(
                    $paths[9],
                ),
        );
    }

    public function test_authorized_download_verifies_and_logs_backup(): void
    {
        Storage::fake('local');

        $user =
            User::factory()->create([
                'role' =>
                    'Admin',
            ]);

        $contents =
            $this->validSql();

        Storage::disk('local')
            ->put(
                'backups/download.sql',
                $contents,
            );

        $backup =
            $this->backup([
                'file_name' =>
                    'download.sql',

                'file_path' =>
                    'backups/download.sql',

                'file_size' =>
                    strlen($contents),
            ]);

        $response =
            $this->actingAs($user)
                ->get(
                    route(
                        'database-backups.download',
                        $backup,
                    ),
                );

        $response->assertOk();

        $backup->refresh();

        $this->assertSame(
            'Verified',
            $backup->verification_status,
        );

        $this->assertDatabaseHas(
            'activity_logs',
            [
                'action' =>
                    'Downloaded',

                'module' =>
                    'Database Backup',

                'subject_id' =>
                    $backup->id,
            ],
        );
    }

    public function test_unauthorized_user_cannot_download_backup(): void
    {
        Storage::fake('local');

        $user =
            User::factory()->create([
                'role' =>
                    'Accountant',

                'permissions' =>
                    [],
            ]);

        $contents =
            $this->validSql();

        Storage::disk('local')
            ->put(
                'backups/private.sql',
                $contents,
            );

        $backup =
            $this->backup([
                'file_name' =>
                    'private.sql',

                'file_path' =>
                    'backups/private.sql',

                'file_size' =>
                    strlen($contents),
            ]);

        $this->actingAs($user)
            ->get(
                route(
                    'database-backups.download',
                    $backup,
                ),
            )
            ->assertForbidden();

        $this->assertDatabaseMissing(
            'activity_logs',
            [
                'action' =>
                    'Downloaded',

                'module' =>
                    'Database Backup',

                'subject_id' =>
                    $backup->id,
            ],
        );
    }

    public function test_scheduled_failure_notifies_backup_admins(): void
    {
        User::factory()->create([
            'role' =>
                'Admin',

            'is_active' =>
                true,
        ]);

        $this->mock(
            DatabaseBackupService::class,
            function (
                MockInterface $mock,
            ): void {
                $mock->shouldReceive(
                    'create',
                )
                    ->once()
                    ->with(
                        'Scheduled',
                    )
                    ->andThrow(
                        new RuntimeException(
                            'Simulated backup failure',
                        ),
                    );
            },
        );

        $this->artisan(
            'kes:daily-backup',
        )->assertExitCode(
            Command::FAILURE,
        );

        $this->assertDatabaseCount(
            'notifications',
            1,
        );

        $data =
            json_decode(
                (string)
                DB::table('notifications')
                    ->value('data'),
                true,
            );

        $this->assertStringContainsString(
            'Scheduled database backup failed',
            (string) (
                $data['title']
                ?? ''
            ),
        );
    }

    public function test_scheduler_uses_unified_service_without_mysqldump(): void
    {
        $command =
            file_get_contents(
                app_path(
                    'Console/Commands/DailyBackupScheduler.php',
                ),
            );

        $schedule =
            file_get_contents(
                base_path(
                    'routes/console.php',
                ),
            );

        $this->assertStringContainsString(
            'DatabaseBackupService',
            $command,
        );

        $this->assertStringContainsString(
            "create(\n                    'Scheduled'",
            $command,
        );

        $this->assertStringNotContainsString(
            'mysqldump',
            $command,
        );

        $this->assertStringNotContainsString(
            "storage_path('app/backups')",
            $command,
        );

        $this->assertStringContainsString(
            "Schedule::command('kes:daily-backup')",
            $schedule,
        );

        $this->assertStringContainsString(
            "->dailyAt('02:00')",
            $schedule,
        );
    }

    private function backup(
        array $overrides = [],
    ): DatabaseBackup {
        return DatabaseBackup::query()
            ->create(
                array_merge(
                    [
                        'backup_no' =>
                            'BK-90001',

                        'database_name' =>
                            'test_database',

                        'file_name' =>
                            'backup.sql',

                        'file_path' =>
                            'backups/backup.sql',

                        'file_size' =>
                            0,

                        'backup_type' =>
                            'Manual',

                        'verification_status' =>
                            'Pending',

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
                '-- Database: test_database',
                '-- Created At: 2026-07-21 01:00:00',
                '',
                'SET FOREIGN_KEY_CHECKS=0;',
                'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";',
                'START TRANSACTION;',
                '',
                'DROP TABLE IF EXISTS `users`;',
                'CREATE TABLE `users` (`id` bigint unsigned NOT NULL);',
                'INSERT INTO `users` (`id`) VALUES (1);',
                str_repeat(
                    '-- backup integrity filler ',
                    10,
                ),
                'COMMIT;',
                'SET FOREIGN_KEY_CHECKS=1;',
                '',
            ],
        );
    }
}
