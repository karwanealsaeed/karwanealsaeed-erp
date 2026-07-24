<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Concerns\HasRbacResourceAuthorization;
use App\Filament\Resources\AccountHeads\AccountHeadResource;
use App\Models\AccountHead;
use App\Models\User;
use App\Support\RbacPermissionRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountHeadRbacAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_head_resource_uses_central_rbac_contract(): void
    {
        $this->assertContains(
            HasRbacResourceAuthorization::class,
            class_uses_recursive(AccountHeadResource::class),
        );

        $this->assertContains(
            AccountHeadResource::class,
            RbacPermissionRegistry::resourceClasses(),
        );
    }

    public function test_director_style_user_can_view_but_cannot_mutate_chart_of_accounts(): void
    {
        $director = User::factory()->create([
            'role' => RbacPermissionRegistry::ROLE_MANAGER,
            'is_active' => true,
            'permissions' => [
                'reports_view',
            ],
        ]);

        $this->actingAs($director);

        $record = new AccountHead;

        $this->assertTrue(AccountHeadResource::canViewAny());
        $this->assertTrue(AccountHeadResource::canView($record));
        $this->assertFalse(AccountHeadResource::canCreate());
        $this->assertFalse(AccountHeadResource::canEdit($record));
        $this->assertFalse(AccountHeadResource::canDelete($record));
        $this->assertFalse(AccountHeadResource::canDeleteAny());
    }

    public function test_accounting_control_user_can_manage_chart_of_accounts(): void
    {
        $accountant = User::factory()->create([
            'role' => RbacPermissionRegistry::ROLE_ACCOUNTANT,
            'is_active' => true,
            'permissions' => [
                RbacPermissionRegistry::ACCOUNTING_MANAGE,
            ],
        ]);

        $this->actingAs($accountant);

        $record = new AccountHead;

        $this->assertTrue(AccountHeadResource::canViewAny());
        $this->assertTrue(AccountHeadResource::canView($record));
        $this->assertTrue(AccountHeadResource::canCreate());
        $this->assertTrue(AccountHeadResource::canEdit($record));
        $this->assertTrue(AccountHeadResource::canDelete($record));
        $this->assertTrue(AccountHeadResource::canDeleteAny());
    }

    public function test_inactive_accounting_user_is_denied(): void
    {
        $inactive = User::factory()->create([
            'role' => RbacPermissionRegistry::ROLE_ACCOUNTANT,
            'is_active' => false,
            'permissions' => [
                RbacPermissionRegistry::ACCOUNTING_MANAGE,
                'reports_view',
            ],
        ]);

        $this->actingAs($inactive);

        $record = new AccountHead;

        $this->assertFalse(AccountHeadResource::canViewAny());
        $this->assertFalse(AccountHeadResource::canView($record));
        $this->assertFalse(AccountHeadResource::canCreate());
        $this->assertFalse(AccountHeadResource::canEdit($record));
        $this->assertFalse(AccountHeadResource::canDelete($record));
        $this->assertFalse(AccountHeadResource::canDeleteAny());
    }
}
