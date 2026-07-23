<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Pages\AccountLedgerReport;
use App\Models\User;
use App\Support\RbacPermissionRegistry;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use ReflectionClass;
use Tests\TestCase;

final class GeneralLedgerVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_panel_registers_general_ledger_route_and_navigation(): void
    {
        $admin =
            $this->user(
                role:
                    RbacPermissionRegistry::ROLE_ADMIN,
                permissions: [],
            );

        Auth::login($admin);

        $panel =
            Filament::getPanel('admin');

        if (
            method_exists(
                Filament::class,
                'setCurrentPanel',
            )
        ) {
            Filament::setCurrentPanel($panel);
        }

        $this->assertContains(
            AccountLedgerReport::class,
            $panel->getPages(),
        );

        $route =
            Route::getRoutes()->getByName(
                'filament.admin.pages.account-ledger-report',
            );

        $this->assertNotNull($route);

        $this->assertSame(
            'admin/account-ledger-report',
            $route->uri(),
        );

        $this->assertTrue(
            AccountLedgerReport::canAccess(),
        );

        $this->assertTrue(
            AccountLedgerReport::shouldRegisterNavigation(),
        );

        $this->assertSame(
            'General Ledger',
            AccountLedgerReport::getNavigationLabel(),
        );

        $this->assertSame(
            'Finance',
            AccountLedgerReport::getNavigationGroup(),
        );

        $this->assertSame(
            'account-ledger-report',
            AccountLedgerReport::getSlug(),
        );

        $items =
            AccountLedgerReport::getNavigationItems();

        $this->assertCount(1, $items);

        $this->assertSame(
            'General Ledger',
            $items[0]->getLabel(),
        );

        $this->assertSame(
            'Finance',
            $items[0]->getGroup(),
        );

        $this->assertSame(
            AccountLedgerReport::getUrl(
                panel: 'admin',
            ),
            $items[0]->getUrl(),
        );
    }

    public function test_general_ledger_page_title_is_clear_and_route_slug_stays_stable(): void
    {
        $reflection =
            new ReflectionClass(
                AccountLedgerReport::class,
            );

        $title =
            $reflection->getProperty(
                'title',
            );

        $this->assertSame(
            'General Ledger',
            $title->getValue(),
        );

        $this->assertSame(
            'account-ledger-report',
            AccountLedgerReport::getSlug(),
        );
    }

    public function test_manager_and_accountant_need_an_allowed_finance_or_report_permission(): void
    {
        $manager =
            $this->user(
                role:
                    RbacPermissionRegistry::ROLE_MANAGER,
                permissions: [
                    RbacPermissionRegistry::ACCOUNTING_MANAGE,
                ],
            );

        Auth::login($manager);

        $this->assertTrue(
            AccountLedgerReport::canAccess(),
        );

        $accountant =
            $this->user(
                role:
                    RbacPermissionRegistry::ROLE_ACCOUNTANT,
                permissions: [
                    'reports_view',
                ],
            );

        Auth::login($accountant);

        $this->assertTrue(
            AccountLedgerReport::canAccess(),
        );

        $managerWithoutPermission =
            $this->user(
                role:
                    RbacPermissionRegistry::ROLE_MANAGER,
                permissions: [],
            );

        Auth::login(
            $managerWithoutPermission,
        );

        $this->assertFalse(
            AccountLedgerReport::canAccess(),
        );
    }

    public function test_sales_staff_cannot_open_general_ledger_even_with_reports_permission(): void
    {
        $salesStaff =
            $this->user(
                role:
                    RbacPermissionRegistry::ROLE_SALES_STAFF,
                permissions: [
                    'reports_view',
                    RbacPermissionRegistry::ACCOUNTING_MANAGE,
                ],
            );

        Auth::login($salesStaff);

        $this->assertFalse(
            AccountLedgerReport::canAccess(),
        );
    }

    private function user(
        string $role,
        array $permissions,
    ): User {
        return User::factory()->create([
            'role' => $role,
            'is_active' => true,
            'permissions' => $permissions,
        ]);
    }
}