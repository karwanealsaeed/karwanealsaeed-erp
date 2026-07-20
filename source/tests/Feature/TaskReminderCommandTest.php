<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskReminderCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_due_reminder_creates_database_notification(): void
    {
        $user = User::factory()->create([
            'role' => 'Sales Staff',
            'is_active' => true,
            'permissions' => ['tasks_view'],
        ]);

        $task = Task::query()->create([
            'title' => 'Call visa customer',
            'assigned_to_id' => $user->id,
            'reminder_at' => now()->subMinute(),
            'due_at' => now()->addHour(),
        ]);

        $this->artisan('tasks:send-reminders')
            ->assertSuccessful();

        $this->assertNotNull(
            $task->fresh()->reminder_sent_at,
        );

        $this->assertDatabaseHas(
            'notifications',
            [
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
            ],
        );
    }

    public function test_reminder_is_not_sent_twice(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        Task::query()->create([
            'title' => 'Confirm payment',
            'assigned_to_id' => $user->id,
            'reminder_at' => now()->subMinute(),
        ]);

        $this->artisan('tasks:send-reminders')
            ->assertSuccessful();

        $this->artisan('tasks:send-reminders')
            ->assertSuccessful();

        $this->assertDatabaseCount(
            'notifications',
            1,
        );
    }

    public function test_future_and_completed_reminders_are_ignored(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        Task::query()->create([
            'title' => 'Future reminder',
            'assigned_to_id' => $user->id,
            'reminder_at' => now()->addHour(),
        ]);

        Task::query()->create([
            'title' => 'Completed reminder',
            'assigned_to_id' => $user->id,
            'status' => Task::STATUS_COMPLETED,
            'reminder_at' => now()->subMinute(),
        ]);

        $this->artisan('tasks:send-reminders')
            ->assertSuccessful();

        $this->assertDatabaseCount(
            'notifications',
            0,
        );
    }

    public function test_creator_receives_unassigned_task_reminder(): void
    {
        $creator = User::factory()->create([
            'is_active' => true,
        ]);

        Task::query()->create([
            'title' => 'Unassigned internal task',
            'created_by_id' => $creator->id,
            'reminder_at' => now()->subMinute(),
        ]);

        $this->artisan('tasks:send-reminders')
            ->assertSuccessful();

        $this->assertDatabaseHas(
            'notifications',
            [
                'notifiable_type' => User::class,
                'notifiable_id' => $creator->id,
            ],
        );
    }

    public function test_inactive_recipient_without_creator_is_skipped(): void
    {
        $user = User::factory()->create([
            'is_active' => false,
        ]);

        $task = Task::query()->create([
            'title' => 'Inactive staff reminder',
            'assigned_to_id' => $user->id,
            'reminder_at' => now()->subMinute(),
        ]);

        $this->artisan('tasks:send-reminders')
            ->assertSuccessful();

        $this->assertDatabaseCount(
            'notifications',
            0,
        );

        $this->assertNull(
            $task->fresh()->reminder_sent_at,
        );
    }

    public function test_changing_reminder_time_resets_sent_state(): void
    {
        $task = Task::query()->create([
            'title' => 'Rescheduled follow-up',
            'reminder_at' => now()->addHour(),
        ]);

        $task->forceFill([
            'reminder_sent_at' => now(),
        ])->saveQuietly();

        $task->update([
            'reminder_at' => now()->addDay(),
        ]);

        $this->assertNull(
            $task->fresh()->reminder_sent_at,
        );
    }

    public function test_dry_run_does_not_send_or_mark_reminder(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $task = Task::query()->create([
            'title' => 'Dry run follow-up',
            'assigned_to_id' => $user->id,
            'reminder_at' => now()->subMinute(),
        ]);

        $this->artisan(
            'tasks:send-reminders',
            ['--dry-run' => true],
        )->assertSuccessful();

        $this->assertDatabaseCount(
            'notifications',
            0,
        );

        $this->assertNull(
            $task->fresh()->reminder_sent_at,
        );
    }
}
