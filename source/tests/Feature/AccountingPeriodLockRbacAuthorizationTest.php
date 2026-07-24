<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Concerns\HasRbacResourceAuthorization;
use App\Filament\Resources\AccountingPeriodLocks\AccountingPeriodLockResource;
use App\Models\AccountingPeriodLock;
use App\Models\User;
use App\Services\AccountingPeriodLockService;
use App\Support\ProposalRoleMapping;
use App\Support\RbacPermissionRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AccountingPeriodLockRbacAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_resource_uses_central_rbac_contract(): void
    {
        $this->assertContains(
            HasRbacResourceAuthorization::class,
            class_uses_recursive(AccountingPeriodLockResource::class),
        );

        $this->assertContains(
            AccountingPeriodLockResource::class,
            RbacPermissionRegistry::resourceClasses(),
        );
    }

    public function test_ticketing_and_director_style_users_have_read_only_resource_access(): void
    {
        foreach (
            [
                ProposalRoleMapping::TICKETING_MANAGER,
                ProposalRoleMapping::DIRECTOR,
            ] as $proposalRole
        ) {
            $user = User::factory()->create([
                'role' => RbacPermissionRegistry::ROLE_MANAGER,
                'is_active' => true,
                'permissions' => ProposalRoleMapping::permissions(
                    $proposalRole,
                ),
            ]);

            $this->actingAs($user);

            $record = new AccountingPeriodLock;

            $this->assertTrue(AccountingPeriodLockResource::canAccess());
            $this->assertTrue(
                AccountingPeriodLockResource::shouldRegisterNavigation(),
            );
            $this->assertTrue(AccountingPeriodLockResource::canViewAny());
            $this->assertTrue(
                AccountingPeriodLockResource::canView($record),
            );
            $this->assertFalse(AccountingPeriodLockResource::canCreate());
            $this->assertFalse(
                AccountingPeriodLockResource::canEdit($record),
            );
            $this->assertFalse(
                AccountingPeriodLockResource::canDelete($record),
            );
        }
    }

    public function test_unrelated_and_inactive_users_are_denied_at_resource_level(): void
    {
        $cases = [
            [
                'role' => RbacPermissionRegistry::ROLE_MANAGER,
                'active' => true,
                'permissions' => ProposalRoleMapping::permissions(
                    ProposalRoleMapping::MARKETING_MANAGER,
                ),
            ],
            [
                'role' => RbacPermissionRegistry::ROLE_SALES_STAFF,
                'active' => true,
                'permissions' => ProposalRoleMapping::permissions(
                    ProposalRoleMapping::CUSTOMER_SUPPORT,
                ),
            ],
            [
                'role' => RbacPermissionRegistry::ROLE_SALES_STAFF,
                'active' => false,
                'permissions' => [
                    'reports_view',
                    'flight_payments_view',
                ],
            ],
        ];

        foreach ($cases as $case) {
            $user = User::factory()->create([
                'role' => $case['role'],
                'is_active' => $case['active'],
                'permissions' => $case['permissions'],
            ]);

            $this->actingAs($user);

            $record = new AccountingPeriodLock;

            $this->assertFalse(AccountingPeriodLockResource::canAccess());
            $this->assertFalse(
                AccountingPeriodLockResource::shouldRegisterNavigation(),
            );
            $this->assertFalse(AccountingPeriodLockResource::canViewAny());
            $this->assertFalse(
                AccountingPeriodLockResource::canView($record),
            );
            $this->assertFalse(AccountingPeriodLockResource::canCreate());
            $this->assertFalse(
                AccountingPeriodLockResource::canEdit($record),
            );
            $this->assertFalse(
                AccountingPeriodLockResource::canDelete($record),
            );
        }
    }

    public function test_direct_routes_enforce_action_specific_resource_authorization(): void
    {
        $record = $this->periodLock();

        foreach (
            [
                ProposalRoleMapping::TICKETING_MANAGER,
                ProposalRoleMapping::DIRECTOR,
            ] as $proposalRole
        ) {
            $user = User::factory()->create([
                'role' => RbacPermissionRegistry::ROLE_MANAGER,
                'is_active' => true,
                'permissions' => ProposalRoleMapping::permissions(
                    $proposalRole,
                ),
            ]);

            $this->actingAs($user);

            $this->get(
                route(
                    'filament.admin.resources.accounting-period-locks.index',
                ),
            )->assertOk();

            $this->get(
                route(
                    'filament.admin.resources.accounting-period-locks.view',
                    ['record' => $record],
                ),
            )->assertOk();

            $this->get(
                route(
                    'filament.admin.resources.accounting-period-locks.create',
                ),
            )->assertForbidden();

            $this->get(
                route(
                    'filament.admin.resources.accounting-period-locks.edit',
                    ['record' => $record],
                ),
            )->assertForbidden();
        }

        foreach (
            [
                ProposalRoleMapping::MARKETING_MANAGER,
                ProposalRoleMapping::CUSTOMER_SUPPORT,
            ] as $proposalRole
        ) {
            $mapping = ProposalRoleMapping::mapping($proposalRole);

            $user = User::factory()->create([
                'role' => $mapping['base_role'],
                'is_active' => true,
                'permissions' => $mapping['permissions'],
            ]);

            $this->actingAs($user);

            $this->get(
                route(
                    'filament.admin.resources.accounting-period-locks.index',
                ),
            )->assertForbidden();

            $this->get(
                route(
                    'filament.admin.resources.accounting-period-locks.create',
                ),
            )->assertForbidden();

            $this->get(
                route(
                    'filament.admin.resources.accounting-period-locks.view',
                    ['record' => $record],
                ),
            )->assertForbidden();

            $this->get(
                route(
                    'filament.admin.resources.accounting-period-locks.edit',
                    ['record' => $record],
                ),
            )->assertForbidden();
        }
    }

    public function test_accounting_control_user_has_full_access(): void
    {
        $user = User::factory()->create([
            'role' => RbacPermissionRegistry::ROLE_ACCOUNTANT,
            'is_active' => true,
            'permissions' => [
                RbacPermissionRegistry::ACCOUNTING_MANAGE,
            ],
        ]);

        $this->actingAs($user);

        $record = $this->periodLock();

        $this->assertTrue(AccountingPeriodLockResource::canAccess());
        $this->assertTrue(AccountingPeriodLockResource::canViewAny());
        $this->assertTrue(
            AccountingPeriodLockResource::canView($record),
        );
        $this->assertTrue(AccountingPeriodLockResource::canCreate());
        $this->assertTrue(
            AccountingPeriodLockResource::canEdit($record),
        );
        $this->assertTrue(
            AccountingPeriodLockResource::canDelete($record),
        );

        $this->get(
            route(
                'filament.admin.resources.accounting-period-locks.index',
            ),
        )->assertOk();

        $this->get(
            route(
                'filament.admin.resources.accounting-period-locks.create',
            ),
        )->assertOk();

        $this->get(
            route(
                'filament.admin.resources.accounting-period-locks.view',
                ['record' => $record],
            ),
        )->assertOk();

        $this->get(
            route(
                'filament.admin.resources.accounting-period-locks.edit',
                ['record' => $record],
            ),
        )->assertOk();
    }

    private function periodLock(): AccountingPeriodLock
    {
        $id = DB::table('accounting_period_locks')->insertGetId([
            'name' => 'RBAC Direct Route Test Lock',
            'period_start' => '2026-08-01',
            'period_end' => '2026-08-31',
            'lock_scope' => AccountingPeriodLockService::SCOPE_GENERAL_LEDGER,
            'status' => 'Open',
            'created_by' => null,
            'locked_by' => null,
            'locked_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return AccountingPeriodLock::query()->findOrFail($id);
    }

    public function test_create_header_action_is_bound_to_create_authorization(): void
    {
        $source = file_get_contents(
            app_path(
                'Filament/Resources/AccountingPeriodLocks/Pages/ListAccountingPeriodLocks.php',
            ),
        );

        $this->assertIsString($source);
        $this->assertStringContainsString(
            'CreateAction::make()',
            $source,
        );
        $this->assertStringContainsString(
            '->hidden(',
            $source,
        );
        $this->assertStringContainsString(
            'AccountingPeriodLockResource::canCreate()',
            $source,
        );
    }
}
