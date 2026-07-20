<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_can_be_created_and_assigned(): void
    {
        $creator = User::factory()->create();
        $assignee = User::factory()->create();

        $this->actingAs($creator);

        $task = Task::query()->create([
            'title' => 'Call customer about visa documents',
            'task_type' => 'Visa Follow-up',
            'priority' => Task::PRIORITY_HIGH,
            'assigned_to_id' => $assignee->id,
            'due_at' => now()->addDay(),
        ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Call customer about visa documents',
            'status' => Task::STATUS_PENDING,
            'assigned_to_id' => $assignee->id,
            'created_by_id' => $creator->id,
        ]);

        $this->assertTrue(
            $task->assignedTo->is($assignee),
        );
    }

    public function test_completed_task_records_completion_details(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $task = Task::query()->create([
            'title' => 'Confirm customer payment',
            'status' => Task::STATUS_COMPLETED,
        ]);

        $this->assertNotNull($task->completed_at);

        $this->assertSame(
            $user->id,
            $task->completed_by_id,
        );
    }

    public function test_reopening_task_clears_completion_details(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $task = Task::query()->create([
            'title' => 'Recheck hotel voucher',
            'status' => Task::STATUS_COMPLETED,
        ]);

        $task->update([
            'status' => Task::STATUS_IN_PROGRESS,
        ]);

        $this->assertNull($task->fresh()->completed_at);
        $this->assertNull($task->fresh()->completed_by_id);
    }

    public function test_overdue_scope_only_returns_open_overdue_tasks(): void
    {
        $overdue = Task::query()->create([
            'title' => 'Overdue customer follow-up',
            'due_at' => now()->subHour(),
        ]);

        Task::query()->create([
            'title' => 'Future follow-up',
            'due_at' => now()->addHour(),
        ]);

        Task::query()->create([
            'title' => 'Completed overdue follow-up',
            'status' => Task::STATUS_COMPLETED,
            'due_at' => now()->subDay(),
        ]);

        $tasks = Task::query()->overdue()->get();

        $this->assertTrue($tasks->contains($overdue));
        $this->assertCount(1, $tasks);
        $this->assertTrue($overdue->is_overdue);
    }

    public function test_due_today_scope_excludes_completed_tasks(): void
    {
        $dueToday = Task::query()->create([
            'title' => 'Today visa follow-up',
            'due_at' => now()->endOfDay(),
        ]);

        Task::query()->create([
            'title' => 'Completed today follow-up',
            'status' => Task::STATUS_COMPLETED,
            'due_at' => now()->addHour(),
        ]);

        $tasks = Task::query()->dueToday()->get();

        $this->assertTrue($tasks->contains($dueToday));
        $this->assertCount(1, $tasks);
        $this->assertTrue($dueToday->is_due_today);
    }

    public function test_reminder_due_scope_returns_due_reminders(): void
    {
        $reminder = Task::query()->create([
            'title' => 'Send payment reminder',
            'reminder_at' => now()->subMinute(),
        ]);

        Task::query()->create([
            'title' => 'Future payment reminder',
            'reminder_at' => now()->addDay(),
        ]);

        $tasks = Task::query()->reminderDue()->get();

        $this->assertTrue($tasks->contains($reminder));
        $this->assertCount(1, $tasks);
    }
}