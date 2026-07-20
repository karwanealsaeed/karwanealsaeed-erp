<?php

namespace Tests\Feature;

use App\Filament\Pages\TaskCalendar;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TaskCalendarPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_calendar_route_is_registered(): void
    {
        $this->assertTrue(
            Route::has(
                'filament.admin.pages.task-calendar',
            ),
        );
    }

    public function test_user_needs_task_view_permission(): void
    {
        $user = User::factory()->create([
            'role' => 'Sales Staff',
            'is_active' => true,
            'permissions' => [],
        ]);

        $this->actingAs($user);

        $this->assertFalse(
            TaskCalendar::canAccess(),
        );

        $user->update([
            'role' => 'Sales Staff',
            'is_active' => true,
            'permissions' => ['tasks_view'],
        ]);

        $this->assertTrue(
            TaskCalendar::canAccess(),
        );
    }

    public function test_authorized_staff_only_see_visible_tasks(): void
    {
        $staff = User::factory()->create([
            'role' => 'Sales Staff',
            'is_active' => true,
            'permissions' => ['tasks_view'],
        ]);

        $otherStaff = User::factory()->create();

        Task::query()->create([
            'title' => 'Visible calendar follow-up',
            'assigned_to_id' => $staff->id,
            'due_at' => now()->addDay(),
        ]);

        Task::query()->create([
            'title' => 'Hidden calendar follow-up',
            'assigned_to_id' => $otherStaff->id,
            'created_by_id' => $otherStaff->id,
            'due_at' => now()->addDay(),
        ]);

        $response = $this
            ->actingAs($staff)
            ->get(
                route(
                    'filament.admin.pages.task-calendar',
                    ['month' => now()->format('Y-m')],
                ),
            );

        $response->assertOk();

        $response->assertSee(
            'Visible calendar follow-up',
        );

        $response->assertDontSee(
            'Hidden calendar follow-up',
        );
    }

    public function test_calendar_uses_start_date_when_due_date_is_missing(): void
    {
        $staff = User::factory()->create([
            'role' => 'Sales Staff',
            'is_active' => true,
            'permissions' => ['tasks_view'],
        ]);

        Task::query()->create([
            'title' => 'Start date calendar task',
            'assigned_to_id' => $staff->id,
            'starts_at' => now()->addDay(),
            'due_at' => null,
        ]);

        $response = $this
            ->actingAs($staff)
            ->get(
                route(
                    'filament.admin.pages.task-calendar',
                    ['month' => now()->format('Y-m')],
                ),
            );

        $response->assertOk();

        $response->assertSee(
            'Start date calendar task',
        );
    }

    public function test_invalid_month_falls_back_safely(): void
    {
        $staff = User::factory()->create([
            'role' => 'Sales Staff',
            'is_active' => true,
            'permissions' => ['tasks_view'],
        ]);

        $response = $this
            ->actingAs($staff)
            ->get(
                route(
                    'filament.admin.pages.task-calendar',
                    ['month' => 'invalid-month'],
                ),
            );

        $response->assertOk();

        $response->assertSee(
            now()->format('F Y'),
        );
    }

    public function test_admin_can_see_all_calendar_tasks(): void
    {
        $admin = User::factory()->create([
            'role' => 'Admin',
            'is_active' => true,
        ]);

        $staff = User::factory()->create();

        Task::query()->create([
            'title' => 'Company-wide admin task',
            'assigned_to_id' => $staff->id,
            'due_at' => now()->addDay(),
        ]);

        $response = $this
            ->actingAs($admin)
            ->get(
                route(
                    'filament.admin.pages.task-calendar',
                    ['month' => now()->format('Y-m')],
                ),
            );

        $response->assertOk();

        $response->assertSee(
            'Company-wide admin task',
        );
    }
}