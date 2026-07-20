<?php

namespace Tests\Feature;

use App\Filament\Pages\SystemHealthCheck;
use App\Models\SystemErrorIncident;
use App\Models\User;
use App\Services\SystemErrorService;
use Error;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\TestCase;

class ErrorMonitoringTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'error_monitoring.enabled'
                => true,

            'error_monitoring.capture_testing'
                => true,

            'error_monitoring.alert_cooldown_minutes'
                => 60,
        ]);
    }

    public function test_error_incident_table_exists(): void
    {
        $this->assertTrue(
            Schema::hasTable(
                'system_error_incidents',
            ),
        );

        foreach (
            [
                'fingerprint',
                'severity',
                'exception_class',
                'message',
                'occurrences',
                'first_seen_at',
                'last_seen_at',
                'last_notified_at',
                'resolved_at',
                'context',
                'sample_trace',
            ]
            as $column
        ) {
            $this->assertTrue(
                Schema::hasColumn(
                    'system_error_incidents',
                    $column,
                ),
                'Missing column: '.$column,
            );
        }
    }

    public function test_duplicate_errors_are_grouped_and_secrets_redacted(): void
    {
        $exception =
            new RuntimeException(
                'Payment failed password=secret token=abc123 order 55',
            );

        $service =
            app(
                SystemErrorService::class,
            );

        $service->capture(
            $exception,
        );

        $service->capture(
            $exception,
        );

        $this->assertDatabaseCount(
            'system_error_incidents',
            1,
        );

        $incident =
            SystemErrorIncident::query()
                ->firstOrFail();

        $this->assertSame(
            2,
            $incident->occurrences,
        );

        $this->assertStringNotContainsString(
            'secret',
            $incident->message,
        );

        $this->assertStringNotContainsString(
            'abc123',
            $incident->message,
        );

        $this->assertStringContainsString(
            '[REDACTED]',
            $incident->message,
        );
    }

    public function test_critical_alert_is_sent_once_during_cooldown(): void
    {
        User::factory()->create([
            'role' =>
                'Admin',

            'is_active' =>
                true,
        ]);

        $exception =
            new Error(
                'Critical accounting failure',
            );

        $service =
            app(
                SystemErrorService::class,
            );

        $service->capture(
            $exception,
        );

        $service->capture(
            $exception,
        );

        $this->assertDatabaseCount(
            'notifications',
            1,
        );

        $incident =
            SystemErrorIncident::query()
                ->firstOrFail();

        $this->assertSame(
            'critical',
            $incident->severity,
        );

        $this->assertSame(
            2,
            $incident->occurrences,
        );

        $this->assertNotNull(
            $incident
                ->last_notified_at,
        );

        $notificationData =
            json_decode(
                (string) DB::table(
                    'notifications',
                )->value('data'),
                true,
            );

        $this->assertStringContainsString(
            'Critical ERP error detected',
            (string) (
                $notificationData['title']
                ?? ''
            ),
        );
    }

    public function test_incident_can_be_resolved_and_reopened(): void
    {
        $user =
            User::factory()->create([
                'role' =>
                    'Admin',

                'is_active' =>
                    true,
            ]);

        $incident =
            app(
                SystemErrorService::class,
            )->capture(
                new RuntimeException(
                    'Resolvable incident',
                ),
            );

        $this->assertNotNull(
            $incident,
        );

        $resolved =
            app(
                SystemErrorService::class,
            )->resolve(
                $incident,
                $user,
                'Fixed configuration.',
            );

        $this->assertNotNull(
            $resolved->resolved_at,
        );

        $this->assertSame(
            $user->id,
            $resolved->resolved_by,
        );

        $reopened =
            app(
                SystemErrorService::class,
            )->reopen(
                $resolved,
            );

        $this->assertNull(
            $reopened->resolved_at,
        );

        $this->assertNull(
            $reopened->resolved_by,
        );
    }

    public function test_validation_exceptions_are_ignored(): void
    {
        $exception =
            ValidationException::withMessages([
                'email' =>
                    'Email is required.',
            ]);

        $result =
            app(
                SystemErrorService::class,
            )->capture(
                $exception,
            );

        $this->assertNull(
            $result,
        );

        $this->assertDatabaseCount(
            'system_error_incidents',
            0,
        );
    }

    public function test_error_monitoring_page_is_protected_and_renders(): void
    {
        $admin =
            User::factory()->create([
                'role' =>
                    'Admin',

                'is_active' =>
                    true,
            ]);

        app(
            SystemErrorService::class,
        )->capture(
            new RuntimeException(
                'Viewer test incident',
            ),
        );

        $this->actingAs(
            $admin,
        )
            ->get(
                route(
                    'filament.admin.pages.error-log-viewer',
                ),
            )
            ->assertOk()
            ->assertSee(
                'Structured Error Monitoring',
            )
            ->assertSee(
                'Viewer test incident',
            );

        $salesUser =
            User::factory()->create([
                'role' =>
                    'Sales Staff',

                'is_active' =>
                    true,
            ]);

        $this->actingAs(
            $salesUser,
        )
            ->get(
                route(
                    'filament.admin.pages.error-log-viewer',
                ),
            )
            ->assertForbidden();
    }

    public function test_system_health_contains_error_monitoring_checks(): void
    {
        $checks =
            app(
                SystemHealthCheck::class,
            )->getChecks();

        $this->assertArrayHasKey(
            'Error Monitoring',
            $checks,
        );

        $group =
            collect(
                $checks[
                    'Error Monitoring'
                ],
            );

        $this->assertTrue(
            $group->contains(
                fn (array $check): bool =>
                    $check['name']
                    === 'Structured Error Table',
            ),
        );

        $this->assertTrue(
            $group->contains(
                fn (array $check): bool =>
                    $check['name']
                    === 'Daily Log Rotation',
            ),
        );
    }

    public function test_maintenance_command_prunes_old_resolved_incidents(): void
    {
        $incident =
            SystemErrorIncident::query()
                ->create([
                    'fingerprint' =>
                        hash(
                            'sha256',
                            'old-resolved',
                        ),

                    'severity' =>
                        'error',

                    'environment' =>
                        'testing',

                    'exception_class' =>
                        RuntimeException::class,

                    'message' =>
                        'Old resolved incident',

                    'occurrences' =>
                        1,

                    'first_seen_at' =>
                        now()
                            ->subDays(40),

                    'last_seen_at' =>
                        now()
                            ->subDays(40),

                    'resolved_at' =>
                        now()
                            ->subDays(35),
                ]);

        $this->artisan(
            'system:maintain-errors',
            [
                '--resolved-days' =>
                    30,
            ],
        )->assertSuccessful();

        $this->assertDatabaseMissing(
            'system_error_incidents',
            [
                'id' =>
                    $incident->id,
            ],
        );
    }

    public function test_bootstrap_schedule_logging_and_dashboard_are_wired(): void
    {
        $bootstrap =
            file_get_contents(
                base_path(
                    'bootstrap/app.php',
                ),
            );

        $schedule =
            file_get_contents(
                base_path(
                    'routes/console.php',
                ),
            );

        $logging =
            file_get_contents(
                config_path(
                    'logging.php',
                ),
            );

        $dashboard =
            file_get_contents(
                resource_path(
                    'views/filament/pages/dashboard.blade.php',
                ),
            );

        $this->assertStringContainsString(
            'SystemErrorService::class',
            $bootstrap,
        );

        $this->assertStringContainsString(
            '$exceptions->report(',
            $bootstrap,
        );

        $this->assertStringContainsString(
            "Schedule::command('system:maintain-errors')",
            $schedule,
        );

        $this->assertStringContainsString(
            "env('LOG_STACK', 'daily')",
            $logging,
        );

        $this->assertStringContainsString(
            '<x-admin-error-health />',
            $dashboard,
        );
    }
}