<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Pages\HrReports;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Support\ProposalRoleMapping;
use App\Support\RbacPermissionRegistry;
use ReflectionClass;
use Tests\TestCase;

final class ProposalRoleMappingTest extends TestCase
{
    public function test_exact_ten_proposal_labels_are_registered(): void
    {
        $this->assertSame(
            [
                'Super Admin',
                'Director',
                'Accountant',
                'Ticketing Manager',
                'Visa Manager',
                'Umrah Manager',
                'HR Manager',
                'Marketing Manager',
                'Customer Support',
                'Customer',
            ],
            ProposalRoleMapping::labels(),
        );

        $this->assertCount(
            10,
            ProposalRoleMapping::mappings(),
        );
    }

    public function test_base_roles_match_the_approved_architecture(): void
    {
        $mappings =
            ProposalRoleMapping::mappings();

        $this->assertSame(
            RbacPermissionRegistry::ROLE_ADMIN,
            $mappings['Super Admin']['base_role'],
        );

        foreach (
            [
                'Director',
                'Ticketing Manager',
                'Visa Manager',
                'Umrah Manager',
                'HR Manager',
                'Marketing Manager',
            ]
            as $label
        ) {
            $this->assertSame(
                RbacPermissionRegistry::ROLE_MANAGER,
                $mappings[$label]['base_role'],
            );
        }

        $this->assertSame(
            RbacPermissionRegistry::ROLE_ACCOUNTANT,
            $mappings['Accountant']['base_role'],
        );

        $this->assertSame(
            RbacPermissionRegistry::ROLE_SALES_STAFF,
            $mappings['Customer Support']['base_role'],
        );

        $this->assertNull(
            $mappings['Customer']['base_role'],
        );
    }

    public function test_permission_modes_prevent_specialized_manager_default_inheritance(): void
    {
        $mappings =
            ProposalRoleMapping::mappings();

        foreach (
            [
                'Director',
                'Ticketing Manager',
                'Visa Manager',
                'Umrah Manager',
                'HR Manager',
                'Marketing Manager',
                'Customer Support',
            ]
            as $label
        ) {
            $this->assertSame(
                'explicit',
                $mappings[$label]['permission_mode'],
            );
        }

        $this->assertSame(
            'base_default',
            $mappings['Accountant']['permission_mode'],
        );

        $this->assertSame(
            'admin_bypass',
            $mappings['Super Admin']['permission_mode'],
        );

        $this->assertSame(
            'portal_identity',
            $mappings['Customer']['permission_mode'],
        );
    }

    public function test_every_mapped_permission_exists_in_the_catalogue(): void
    {
        $catalogue =
            UserForm::getPermissionOptions();

        foreach (
            ProposalRoleMapping::mappings()
            as $label => $mapping
        ) {
            $this->assertSame(
                array_values(
                    array_unique(
                        $mapping['permissions'],
                    ),
                ),
                $mapping['permissions'],
                "{$label} must not contain duplicate permissions.",
            );

            foreach ($mapping['permissions'] as $permission) {
                $this->assertArrayHasKey(
                    $permission,
                    $catalogue,
                    "{$label} contains unknown permission {$permission}.",
                );
            }
        }
    }

    public function test_reviewed_specialized_bundle_sizes_are_stable(): void
    {
        $this->assertCount(
            30,
            ProposalRoleMapping::permissions('Director'),
        );

        $this->assertCount(
            35,
            ProposalRoleMapping::permissions('Ticketing Manager'),
        );

        $this->assertCount(
            23,
            ProposalRoleMapping::permissions('Visa Manager'),
        );

        $this->assertCount(
            39,
            ProposalRoleMapping::permissions('Umrah Manager'),
        );

        $this->assertCount(
            46,
            ProposalRoleMapping::permissions('HR Manager'),
        );

        $this->assertCount(
            24,
            ProposalRoleMapping::permissions('Marketing Manager'),
        );

        $this->assertCount(
            14,
            ProposalRoleMapping::permissions('Customer Support'),
        );
    }

    public function test_director_bundle_is_read_only_and_excludes_system_control(): void
    {
        $permissions =
            ProposalRoleMapping::permissions('Director');

        foreach ($permissions as $permission) {
            $this->assertDoesNotMatchRegularExpression(
                '/_(create|edit|delete|manage|approve|restore)$/',
                $permission,
            );
        }

        foreach (
            [
                'users_manage',
                'settings_manage',
                'database_backups_restore',
                'accounting_manage',
            ]
            as $forbidden
        ) {
            $this->assertNotContains(
                $forbidden,
                $permissions,
            );
        }
    }

    public function test_operational_specialized_roles_have_no_delete_permissions(): void
    {
        foreach (
            [
                'Ticketing Manager',
                'Visa Manager',
                'Umrah Manager',
                'Customer Support',
            ]
            as $label
        ) {
            foreach (
                ProposalRoleMapping::permissions($label)
                as $permission
            ) {
                $this->assertFalse(
                    str_ends_with(
                        $permission,
                        '_delete',
                    ),
                    "{$label} must not receive delete permission.",
                );
            }
        }
    }

    public function test_hr_manager_has_all_dedicated_hr_permissions_and_report_access(): void
    {
        $hrPermissions =
            array_keys(
                UserForm::getPermissionGroups()[
                    'permissions_hr'
                ]['options'],
            );

        $mapped =
            ProposalRoleMapping::permissions('HR Manager');

        $this->assertCount(
            45,
            $hrPermissions,
        );

        foreach ($hrPermissions as $permission) {
            $this->assertContains(
                $permission,
                $mapped,
            );
        }

        $this->assertContains(
            'reports_view',
            $mapped,
        );
    }

    public function test_hr_reports_rule_uses_staff_and_reports_permissions_only(): void
    {
        $reflection =
            new ReflectionClass(
                RbacPermissionRegistry::class,
            );

        $rules =
            $reflection
                ->getReflectionConstant(
                    'PAGE_RULES',
                )
                ?->getValue();

        $this->assertIsArray($rules);

        $this->assertSame(
            [
                RbacPermissionRegistry::ROLE_MANAGER,
            ],
            $rules[HrReports::class]['roles'],
        );

        $this->assertSame(
            [
                'staff_view',
                'reports_view',
            ],
            $rules[HrReports::class]['permissions'],
        );

        $this->assertNotContains(
            'users_view',
            $rules[HrReports::class]['permissions'],
        );

        $this->assertNotContains(
            'users_manage',
            $rules[HrReports::class]['permissions'],
        );
    }

    public function test_accountant_admin_and_customer_contracts_remain_separate(): void
    {
        $this->assertSame(
            UserForm::getDefaultPermissions(
                RbacPermissionRegistry::ROLE_ACCOUNTANT,
            ),
            ProposalRoleMapping::permissions('Accountant'),
        );

        $this->assertSame(
            [],
            ProposalRoleMapping::permissions('Super Admin'),
        );

        $this->assertSame(
            [],
            ProposalRoleMapping::permissions('Customer'),
        );

        $this->assertSame(
            'customer_portal',
            ProposalRoleMapping::mapping(
                'Customer',
            )['identity'],
        );
    }
}