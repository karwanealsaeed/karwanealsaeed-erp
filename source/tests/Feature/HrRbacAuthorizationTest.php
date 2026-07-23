<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Concerns\HasRbacResourceAuthorization;
use App\Filament\Resources\Departments\DepartmentResource;
use App\Filament\Resources\Designations\DesignationResource;
use App\Filament\Resources\LeaveTypes\LeaveTypeResource;
use App\Filament\Resources\Staff\StaffResource;
use App\Filament\Resources\StaffAttendances\StaffAttendanceResource;
use App\Filament\Resources\StaffDocuments\StaffDocumentResource;
use App\Filament\Resources\StaffLeaves\StaffLeaveResource;
use App\Filament\Resources\StaffLedgerEntries\StaffLedgerEntryResource;
use App\Filament\Resources\StaffMembers\StaffMemberResource;
use App\Filament\Resources\StaffPayrolls\StaffPayrollResource;
use App\Filament\Resources\StaffPerformanceNotes\StaffPerformanceNoteResource;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Models\User;
use App\Support\RbacPermissionRegistry;
use Tests\TestCase;

final class HrRbacAuthorizationTest extends TestCase
{
    public function test_all_hr_resources_use_central_trait_and_registry(): void
    {
        $registered =
            RbacPermissionRegistry::resourceClasses();

        foreach ($this->resources() as $resourceClass => $key) {
            $this->assertContains(
                HasRbacResourceAuthorization::class,
                $this->recursiveClassUses(
                    $resourceClass,
                ),
                "{$resourceClass} must use central RBAC.",
            );

            $this->assertContains(
                $resourceClass,
                $registered,
                "{$resourceClass} must be registered.",
            );
        }
    }

    public function test_hr_permission_catalogue_and_manager_preset_are_complete(): void
    {
        $groups =
            UserForm::getPermissionGroups();

        $options =
            UserForm::getPermissionOptions();

        $manager =
            UserForm::getDefaultPermissions(
                RbacPermissionRegistry::ROLE_MANAGER,
            );

        $accountant =
            UserForm::getDefaultPermissions(
                RbacPermissionRegistry::ROLE_ACCOUNTANT,
            );

        $sales =
            UserForm::getDefaultPermissions(
                RbacPermissionRegistry::ROLE_SALES_STAFF,
            );

        $this->assertArrayHasKey(
            'permissions_hr',
            $groups,
        );

        foreach ($this->permissions() as $permission) {
            $this->assertArrayHasKey(
                $permission,
                $options,
                "Missing HR permission: {$permission}",
            );

            $this->assertContains(
                $permission,
                $manager,
                "Manager missing HR permission: {$permission}",
            );

            $this->assertNotContains(
                $permission,
                $accountant,
                "Accountant inherited HR permission: {$permission}",
            );

            $this->assertNotContains(
                $permission,
                $sales,
                "Sales Staff inherited HR permission: {$permission}",
            );
        }
    }

    public function test_manager_needs_exact_hr_permission_and_other_roles_are_rejected(): void
    {
        foreach ($this->resources() as $resourceClass => $key) {
            foreach (
                [
                    'view',
                    'create',
                    'edit',
                    'delete',
                ]
                as $action
            ) {
                $permission =
                    "{$key}_{$action}";

                $this->assertTrue(
                    RbacPermissionRegistry::allowsResourceAction(
                        $this->user(
                            RbacPermissionRegistry::ROLE_MANAGER,
                            [$permission],
                        ),
                        $resourceClass,
                        $action,
                    ),
                );

                $this->assertFalse(
                    RbacPermissionRegistry::allowsResourceAction(
                        $this->user(
                            RbacPermissionRegistry::ROLE_MANAGER,
                            [],
                        ),
                        $resourceClass,
                        $action,
                    ),
                );

                $this->assertFalse(
                    RbacPermissionRegistry::allowsResourceAction(
                        $this->user(
                            RbacPermissionRegistry::ROLE_ACCOUNTANT,
                            [$permission],
                        ),
                        $resourceClass,
                        $action,
                    ),
                );

                $this->assertFalse(
                    RbacPermissionRegistry::allowsResourceAction(
                        $this->user(
                            RbacPermissionRegistry::ROLE_SALES_STAFF,
                            [$permission],
                        ),
                        $resourceClass,
                        $action,
                    ),
                );
            }
        }
    }

    public function test_legacy_users_permissions_no_longer_grant_hr_access(): void
    {
        $legacy =
            [
                'users_manage',
                'users_view',
                'users_create',
                'users_edit',
                'users_delete',
            ];

        foreach ($this->resources() as $resourceClass => $key) {
            foreach (
                [
                    'view',
                    'create',
                    'edit',
                    'delete',
                ]
                as $action
            ) {
                $this->assertFalse(
                    RbacPermissionRegistry::allowsResourceAction(
                        $this->user(
                            RbacPermissionRegistry::ROLE_MANAGER,
                            $legacy,
                        ),
                        $resourceClass,
                        $action,
                    ),
                    "Legacy users permissions must not grant {$key}_{$action}.",
                );
            }
        }
    }

    public function test_admin_bypass_and_payroll_print_normalization_remain_valid(): void
    {
        foreach ($this->resources() as $resourceClass => $key) {
            foreach (
                [
                    'view',
                    'create',
                    'edit',
                    'delete',
                ]
                as $action
            ) {
                $this->assertTrue(
                    RbacPermissionRegistry::allowsResourceAction(
                        $this->user(
                            RbacPermissionRegistry::ROLE_ADMIN,
                            [],
                        ),
                        $resourceClass,
                        $action,
                    ),
                    "Admin bypass failed for {$key}_{$action}.",
                );
            }
        }

        $this->assertSame(
            'staff_payrolls_print',
            RbacPermissionRegistry::normalizedPermission(
                'staff-payrolls.print',
            ),
        );
    }

    public function test_legacy_resource_methods_were_removed_and_is_active_is_authoritative(): void
    {
        $legacyPaths = [
            app_path(
                'Filament/Resources/StaffAttendances/StaffAttendanceResource.php',
            ),
            app_path(
                'Filament/Resources/StaffMembers/StaffMemberResource.php',
            ),
            app_path(
                'Filament/Resources/StaffPayrolls/StaffPayrollResource.php',
            ),
        ];

        foreach ($legacyPaths as $path) {
            $source =
                file_get_contents($path);

            $this->assertIsString($source);

            $this->assertStringNotContainsString(
                "hasPermission('users_view')",
                $source,
            );

            $this->assertStringNotContainsString(
                "hasPermission('users_create')",
                $source,
            );

            $this->assertStringNotContainsString(
                "hasPermission('users_edit')",
                $source,
            );

            $this->assertStringNotContainsString(
                "hasPermission('users_delete')",
                $source,
            );
        }

        $registry =
            file_get_contents(
                app_path(
                    'Support/RbacPermissionRegistry.php',
                ),
            );

        $form =
            file_get_contents(
                app_path(
                    'Filament/Resources/Users/Schemas/UserForm.php',
                ),
            );

        $this->assertIsString($registry);
        $this->assertIsString($form);

        $this->assertStringContainsString(
            '$user->is_active',
            $registry,
        );

        $this->assertStringContainsString(
            "Toggle::make('is_active')",
            $form,
        );
    }

    /**
     * @return array<class-string, string>
     */
    private function resources(): array
    {
        return [
            DepartmentResource::class =>
                'departments',
            DesignationResource::class =>
                'designations',
            LeaveTypeResource::class =>
                'leave_types',
            StaffResource::class =>
                'staff',
            StaffAttendanceResource::class =>
                'staff_attendances',
            StaffDocumentResource::class =>
                'staff_documents',
            StaffLeaveResource::class =>
                'staff_leaves',
            StaffLedgerEntryResource::class =>
                'staff_ledger_entries',
            StaffMemberResource::class =>
                'staff_members',
            StaffPayrollResource::class =>
                'staff_payrolls',
            StaffPerformanceNoteResource::class =>
                'staff_performance_notes',
        ];
    }

    /**
     * @return list<string>
     */
    private function permissions(): array
    {
        $permissions = [];

        foreach ($this->resources() as $key) {
            foreach (
                [
                    'view',
                    'create',
                    'edit',
                    'delete',
                ]
                as $action
            ) {
                $permissions[] =
                    "{$key}_{$action}";
            }
        }

        $permissions[] =
            'staff_payrolls_print';

        return $permissions;
    }

    /**
     * @return list<class-string>
     */
    private function recursiveClassUses(
        string $class,
    ): array {
        $results = [];

        do {
            $results +=
                class_uses($class)
                ?: [];
        } while (
            $class =
                get_parent_class($class)
        );

        foreach ($results as $trait) {
            $results +=
                $this->recursiveClassUses(
                    $trait,
                );
        }

        return array_values(
            array_unique($results),
        );
    }

    private function user(
        string $role,
        array $permissions,
    ): User {
        $user =
            new User();

        $user->forceFill([
            'role' =>
                $role,
            'permissions' =>
                $permissions,
            'is_active' =>
                true,
        ]);

        return $user;
    }
}