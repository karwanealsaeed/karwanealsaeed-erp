<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class TaskDashboardComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_staff_only_see_their_visible_tasks(): void
    {
        $staff = User::factory()->create([
            'permissions' => [
                'tasks_view',
                'tasks_edit',
            ],
        ]);

        $otherStaff = User::factory()->create();

        $this->actingAs($staff);

        Task::query()->create([
            'title' => 'My customer follow-up',
            'assigned_to_id' => $staff->id,
            'created_by_id' => $staff->id,
            'due_at' => now()->addDay(),
        ]);

        Task::query()->create([
            'title' => 'Another staff task',
            'assigned_to_id' => $otherStaff->id,
            'created_by_id' => $otherStaff->id,
            'due_at' => now()->addDay(),
        ]);

        $html = Blade::render(
            '<x-admin-task-dashboard />',
        );

        $this->assertStringContainsString(
            'My customer follow-up',
            $html,
        );

        $this->assertStringNotContainsString(
            'Another staff task',
            $html,
        );
    }

    public function test_dashboard_displays_task_statistics(): void
    {
        $staff = User::factory()->create([
            'permissions' => ['tasks_view'],
        ]);

        $this->actingAs($staff);

        Task::query()->create([
            'title' => 'Overdue follow-up',
            'assigned_to_id' => $staff->id,
            'due_at' => now()->subHour(),
            'reminder_at' => now()->subMinute(),
        ]);

        Task::query()->create([
            'title' => 'Today follow-up',
            'assigned_to_id' => $staff->id,
            'due_at' => now()->addHour(),
        ]);

        Task::query()->create([
            'title' => 'Completed task',
            'assigned_to_id' => $staff->id,
            'status' => Task::STATUS_COMPLETED,
            'due_at' => now()->subDay(),
        ]);

        $html = Blade::render(
            '<x-admin-task-dashboard />',
        );

        $this->assertStringContainsString(
            'Open Tasks',
            $html,
        );

        $this->assertStringContainsString(
            'Overdue',
            $html,
        );

        $this->assertStringContainsString(
            'Due Today',
            $html,
        );

        $this->assertStringContainsString(
            'Reminders Due',
            $html,
        );

        $this->assertStringContainsString(
            '>2<',
            str_replace(
                ["\r", "\n", " "],
                '',
                $html,
            ),
        );
    }

    public function test_dashboard_component_is_hidden_without_task_permission(): void
    {
        $user = User::factory()->create([
            'permissions' => [],
        ]);

        $this->actingAs($user);

        Task::query()->create([
            'title' => 'Hidden dashboard task',
            'assigned_to_id' => $user->id,
        ]);

        $html = Blade::render(
            '<x-admin-task-dashboard />',
        );

        $this->assertStringNotContainsString(
            'Tasks & Follow-ups',
            $html,
        );

        $this->assertStringNotContainsString(
            'Hidden dashboard task',
            $html,
        );
    }

    public function test_custom_dashboard_contains_task_component(): void
    {
        $dashboard = file_get_contents(
            resource_path(
                'views/filament/pages/dashboard.blade.php',
            ),
        );

        $this->assertStringContainsString(
            '<x-admin-task-dashboard />',
            $dashboard,
        );
    }
}