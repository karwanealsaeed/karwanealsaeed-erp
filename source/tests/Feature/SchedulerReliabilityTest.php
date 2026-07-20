<?php

namespace Tests\Feature;

use App\Filament\Pages\SystemHealthCheck;
use App\Models\SchedulerHeartbeat;
use App\Models\User;
use App\Services\SchedulerHealthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SchedulerReliabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_scheduler_heartbeat_table_exists(): void
    {
        $this->assertTrue(
            Schema::hasTable(
                'scheduler_heartbeats',
            ),
        );

        foreach (
            [
                'monitor_key',
                'last_started_at',
                'last_finished_at',
                'last_success_at',
                'last_failed_at',
                'last_duration_ms',
                'last_exit_code',
                'consecutive_failures',
                'failure_message',
                'last_alerted_at',
            ]
            as $column
        ) {
            $this->assertTrue(
                Schema::hasColumn(
                    'scheduler_heartbeats',
                    $column,
                ),
                'Missing column: '.$column,
            );
        }
    }

    public function test_heartbeat_command_records_success(): void
    {
        $this->artisan(
            'system:scheduler-heartbeat',
        )->assertSuccessful();

        $heartbeat =
            SchedulerHeartbeat::query()
                ->firstWhere(
                    'monitor_key',
                    SchedulerHealthService
                        ::MONITOR_KEY,
                );

        $this->assertNotNull(
            $heartbeat,
        );

        $this->assertNotNull(
            $heartbeat
                ->last_success_at,
        );

        $this->assertSame(
            0,
            $heartbeat
                ->last_exit_code,
        );

        $this->assertSame(
            0,
            $heartbeat
                ->consecutive_failures,
        );
    }

    public function test_recent_heartbeat_is_healthy(): void
    {
        SchedulerHeartbeat::query()
            ->create([
                'monitor_key' =>
                    SchedulerHealthService
                        ::MONITOR_KEY,

                'last_success_at' =>
                    now(),

                'last_exit_code' =>
                    0,

                'consecutive_failures' =>
                    0,
            ]);

        $status =
            app(
                SchedulerHealthService::class,
            )->status();

        $this->assertTrue(
            $status['healthy'],
        );

        $this->assertSame(
            'Healthy',
            $status['state'],
        );
    }

    public function test_old_heartbeat_is_stale(): void
    {
        config([
            'scheduler_health.stale_after_minutes'
                => 3,
        ]);

        SchedulerHeartbeat::query()
            ->create([
                'monitor_key' =>
                    SchedulerHealthService
                        ::MONITOR_KEY,

                'last_success_at' =>
                    now()
                        ->subMinutes(10),

                'last_exit_code' =>
                    0,

                'consecutive_failures' =>
                    0,
            ]);

        $status =
            app(
                SchedulerHealthService::class,
            )->status();

        $this->assertFalse(
            $status['healthy'],
        );

        $this->assertSame(
            'Stale',
            $status['state'],
        );
    }

    public function test_stale_alert_is_sent_once_during_cooldown(): void
    {
        config([
            'scheduler_health.stale_after_minutes'
                => 3,

            'scheduler_health.alert_cooldown_minutes'
                => 360,
        ]);

        User::factory()->create([
            'role' =>
                'Admin',

            'is_active' =>
                true,
        ]);

        SchedulerHeartbeat::query()
            ->create([
                'monitor_key' =>
                    SchedulerHealthService
                        ::MONITOR_KEY,

                'last_success_at' =>
                    now()
                        ->subMinutes(10),
            ]);

        $service =
            app(
                SchedulerHealthService::class,
            );

        $this->assertTrue(
            $service->alertIfStale(),
        );

        $this->assertFalse(
            $service->alertIfStale(),
        );

        $this->assertDatabaseCount(
            'notifications',
            1,
        );

        $data =
            json_decode(
                (string) DB::table(
                    'notifications',
                )->value('data'),
                true,
            );

        $this->assertStringContainsString(
            'Scheduler health warning',
            (string) (
                $data['title']
                ?? ''
            ),
        );
    }

    public function test_recovery_notification_is_sent_after_alert(): void
    {
        User::factory()->create([
            'role' =>
                'Admin',

            'is_active' =>
                true,
        ]);

        $heartbeat =
            SchedulerHeartbeat::query()
                ->create([
                    'monitor_key' =>
                        SchedulerHealthService
                            ::MONITOR_KEY,

                    'last_success_at' =>
                        now()
                            ->subMinutes(10),

                    'last_alerted_at' =>
                        now(),
                ]);

        app(
            SchedulerHealthService::class,
        )->markSuccess(
            now()->subSecond(),
        );

        $heartbeat->refresh();

        $this->assertNull(
            $heartbeat
                ->last_alerted_at,
        );

        $this->assertDatabaseCount(
            'notifications',
            1,
        );

        $data =
            json_decode(
                (string) DB::table(
                    'notifications',
                )->value('data'),
                true,
            );

        $this->assertStringContainsString(
            'Scheduler recovered',
            (string) (
                $data['title']
                ?? ''
            ),
        );
    }

    public function test_system_health_reports_scheduler_and_queue(): void
    {
        $user =
            User::factory()->create([
                'role' =>
                    'Admin',

                'is_active' =>
                    true,
            ]);

        $this->actingAs($user);

        SchedulerHeartbeat::query()
            ->create([
                'monitor_key' =>
                    SchedulerHealthService
                        ::MONITOR_KEY,

                'last_success_at' =>
                    now(),

                'last_exit_code' =>
                    0,
            ]);

        $checks =
            app(
                SystemHealthCheck::class,
            )->getChecks();

        $this->assertArrayHasKey(
            'Scheduler & Queue',
            $checks,
        );

        $group =
            collect(
                $checks[
                    'Scheduler & Queue'
                ],
            );

        $this->assertTrue(
            $group->contains(
                fn (array $check): bool =>
                    $check['name']
                    === 'Laravel Scheduler Heartbeat'
                    && $check['status']
                    === true,
            ),
        );

        $this->assertTrue(
            $group->contains(
                fn (array $check): bool =>
                    $check['name']
                    === 'Failed Queue Jobs'
                    && $check['status']
                    === true,
            ),
        );
    }

    public function test_schedule_and_dashboard_are_wired(): void
    {
        $schedule =
            file_get_contents(
                base_path(
                    'routes/console.php',
                ),
            );

        $dashboard =
            file_get_contents(
                resource_path(
                    'views/filament/pages/dashboard.blade.php',
                ),
            );

        $this->assertStringContainsString(
            "Schedule::command('system:scheduler-heartbeat')",
            $schedule,
        );

        $this->assertStringContainsString(
            "->everyMinute()",
            $schedule,
        );

        $this->assertStringContainsString(
            '<x-admin-scheduler-health />',
            $dashboard,
        );
    }

    public function test_queue_tables_are_ready_and_empty(): void
    {
        $status =
            app(
                SchedulerHealthService::class,
            )->queueStatus();

        $this->assertTrue(
            $status['tables_ready'],
        );

        $this->assertSame(
            0,
            $status['pending_jobs'],
        );

        $this->assertSame(
            0,
            $status['failed_jobs'],
        );

        $this->assertTrue(
            $status['pending_healthy'],
        );

        $this->assertTrue(
            $status['failed_healthy'],
        );
    }
}