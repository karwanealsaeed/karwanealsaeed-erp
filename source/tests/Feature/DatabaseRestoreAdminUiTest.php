<?php

namespace Tests\Feature;

use App\Models\DatabaseBackup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseRestoreAdminUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_render_database_backup_page_with_restore_action(): void
    {
        $admin =
            User::factory()
                ->create([
                    'role' =>
                        'Admin',

                    'is_active' =>
                        true,

                    'permissions' =>
                        [],
                ]);

        DatabaseBackup::query()
            ->create([
                'backup_no' =>
                    'BK-92001',

                'database_name' =>
                    'testing_database',

                'file_name' =>
                    'BK-92001.sql',

                'file_path' =>
                    'backups/BK-92001.sql',

                'file_size' =>
                    2048,

                'backup_type' =>
                    'Manual',

                'checksum_sha256' =>
                    str_repeat(
                        'a',
                        64,
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

        $this->actingAs(
            $admin,
        )
            ->get(
                route(
                    'filament.admin.resources.database-backups.index',
                ),
            )
            ->assertOk()
            ->assertSee(
                'Database Backups',
            );
    }

    public function test_restore_action_requires_three_factor_confirmation(): void
    {
        $contents =
            $this->tableContents();

        $this->assertStringContainsString(
            "Action::make('restore_database')",
            $contents,
        );

        $this->assertStringContainsString(
            "'confirmation'",
            $contents,
        );

        $this->assertStringContainsString(
            "'password'",
            $contents,
        );

        $this->assertStringContainsString(
            "'acknowledge'",
            $contents,
        );

        $this->assertStringContainsString(
            'Hash::check(',
            $contents,
        );

        $this->assertStringContainsString(
            'ValidationException::withMessages',
            $contents,
        );
    }

    public function test_restore_action_is_permission_protected_and_environment_locked(): void
    {
        $contents =
            $this->tableContents();

        $this->assertStringContainsString(
            'database_backups.restore',
            $contents,
        );

        $this->assertStringContainsString(
            'database_restore.execution_enabled',
            $contents,
        );

        $this->assertStringContainsString(
            '->disabled(',
            $contents,
        );

        $this->assertStringContainsString(
            'requestedBy: $user->id',
            $contents,
        );
    }

    public function test_restore_modal_prevents_accidental_closing_and_executes_service(): void
    {
        $contents =
            $this->tableContents();

        $this->assertStringContainsString(
            'closeModalByClickingAway(',
            $contents,
        );

        $this->assertStringContainsString(
            'closeModalByEscaping(',
            $contents,
        );

        $this->assertStringContainsString(
            'Create Safety Backup and Restore',
            $contents,
        );

        $this->assertStringContainsString(
            'DatabaseRestoreService::class',
            $contents,
        );

        $this->assertStringContainsString(
            ')->execute(',
            $contents,
        );
    }

    public function test_readiness_message_uses_runtime_execution_state(): void
    {
        $contents =
            $this->tableContents();

        $this->assertStringContainsString(
            "\$plan['execution_enabled']",
            $contents,
        );

        $this->assertStringContainsString(
            'Restore execution enabled hai.',
            $contents,
        );

        $this->assertStringContainsString(
            'Actual execution abhi locked hai.',
            $contents,
        );
    }

    private function tableContents(): string
    {
        $contents =
            file_get_contents(
                app_path(
                    'Filament/Resources/DatabaseBackups/Tables/DatabaseBackupsTable.php',
                ),
            );

        $this->assertIsString(
            $contents,
        );

        return $contents;
    }
}