<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\Schemas\UserForm;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TaskResourceTest extends TestCase
{
    public function test_task_resource_routes_are_registered(): void
    {
        $this->assertTrue(
            Route::has(
                'filament.admin.resources.tasks.index',
            ),
        );

        $this->assertTrue(
            Route::has(
                'filament.admin.resources.tasks.create',
            ),
        );

        $this->assertTrue(
            Route::has(
                'filament.admin.resources.tasks.view',
            ),
        );

        $this->assertTrue(
            Route::has(
                'filament.admin.resources.tasks.edit',
            ),
        );
    }

    public function test_task_permissions_are_registered(): void
    {
        $options = UserForm::getPermissionOptions();

        $this->assertArrayHasKey(
            'tasks_view',
            $options,
        );

        $this->assertArrayHasKey(
            'tasks_create',
            $options,
        );

        $this->assertArrayHasKey(
            'tasks_edit',
            $options,
        );

        $this->assertArrayHasKey(
            'tasks_delete',
            $options,
        );
    }

    public function test_manager_receives_all_task_permissions(): void
    {
        $permissions =
            UserForm::getDefaultPermissions('Manager');

        $this->assertContains(
            'tasks_view',
            $permissions,
        );

        $this->assertContains(
            'tasks_create',
            $permissions,
        );

        $this->assertContains(
            'tasks_edit',
            $permissions,
        );

        $this->assertContains(
            'tasks_delete',
            $permissions,
        );
    }

    public function test_sales_staff_receive_operational_task_permissions(): void
    {
        $permissions =
            UserForm::getDefaultPermissions('Sales Staff');

        $this->assertContains(
            'tasks_view',
            $permissions,
        );

        $this->assertContains(
            'tasks_create',
            $permissions,
        );

        $this->assertContains(
            'tasks_edit',
            $permissions,
        );

        $this->assertNotContains(
            'tasks_delete',
            $permissions,
        );
    }

    public function test_dotted_task_permissions_are_normalized(): void
    {
        $permissions =
            UserForm::normalizePermissions([
                'tasks.view',
                'tasks_create',
            ]);

        $this->assertSame(
            [
                'tasks_view',
                'tasks_create',
            ],
            $permissions,
        );
    }
}