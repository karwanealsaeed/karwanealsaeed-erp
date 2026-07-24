<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Pages\AccountingApprovalCenter;
use App\Filament\Pages\AccountingPostingAudit;
use App\Filament\Pages\BankReconciliation;
use App\Filament\Pages\HrReports;
use App\Filament\Pages\StaffPayrollReport;
use App\Filament\Resources\AccountHeads\AccountHeadResource;
use App\Filament\Resources\AccountingPeriodLocks\AccountingPeriodLockResource;
use App\Filament\Resources\AccountVouchers\AccountVoucherResource;
use App\Filament\Resources\BankStatementEntries\BankStatementEntryResource;
use App\Filament\Resources\SupplierBills\SupplierBillResource;
use App\Models\AccountVoucher;
use App\Models\User;
use App\Support\ProposalRoleMapping;
use App\Support\RbacPermissionRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UmrahManagerSensitiveAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_umrah_manager_is_denied_sensitive_cross_department_access(): void
    {
        $user = $this->proposalUser(
            ProposalRoleMapping::UMRAH_MANAGER,
        );

        $this->actingAs($user);

        $voucher = new AccountVoucher([
            'status' => 'Draft',
        ]);

        $this->assertFalse(AccountVoucherResource::canAccess());
        $this->assertFalse(
            AccountVoucherResource::shouldRegisterNavigation(),
        );
        $this->assertFalse(AccountVoucherResource::canViewAny());
        $this->assertFalse(AccountVoucherResource::canView($voucher));
        $this->assertFalse(AccountVoucherResource::canCreate());
        $this->assertFalse(AccountVoucherResource::canEdit($voucher));
        $this->assertFalse(AccountVoucherResource::canDelete($voucher));
        $this->assertFalse(AccountVoucherResource::canDeleteAny());
        $this->assertFalse(HrReports::canAccess());
        $this->assertFalse(StaffPayrollReport::canAccess());
    }

    public function test_umrah_operational_finance_contract_is_preserved(): void
    {
        $user = $this->proposalUser(
            ProposalRoleMapping::UMRAH_MANAGER,
        );

        $this->actingAs($user);

        $this->assertTrue(AccountHeadResource::canViewAny());
        $this->assertFalse(AccountHeadResource::canCreate());

        $this->assertTrue(
            AccountingPeriodLockResource::canViewAny(),
        );
        $this->assertFalse(
            AccountingPeriodLockResource::canCreate(),
        );

        $this->assertTrue(
            BankStatementEntryResource::canViewAny(),
        );
        $this->assertTrue(
            BankStatementEntryResource::canCreate(),
        );
        $this->assertFalse(
            BankStatementEntryResource::canDeleteAny(),
        );

        $this->assertTrue(SupplierBillResource::canViewAny());
        $this->assertTrue(SupplierBillResource::canCreate());
        $this->assertFalse(SupplierBillResource::canDeleteAny());

        $this->assertTrue(BankReconciliation::canAccess());
        $this->assertTrue(AccountingApprovalCenter::canAccess());
        $this->assertTrue(AccountingPostingAudit::canAccess());
    }

    public function test_hr_manager_keeps_hr_and_payroll_reports(): void
    {
        $user = $this->proposalUser(
            ProposalRoleMapping::HR_MANAGER,
        );

        $this->actingAs($user);

        $this->assertTrue(HrReports::canAccess());
        $this->assertTrue(StaffPayrollReport::canAccess());
    }

    public function test_accounting_control_keeps_voucher_access(): void
    {
        $user = User::factory()->create([
            'role' => RbacPermissionRegistry::ROLE_ACCOUNTANT,
            'is_active' => true,
            'permissions' => [
                RbacPermissionRegistry::ACCOUNTING_MANAGE,
            ],
        ]);

        $this->actingAs($user);

        $voucher = new AccountVoucher([
            'status' => 'Draft',
        ]);

        $this->assertTrue(AccountVoucherResource::canAccess());
        $this->assertTrue(
            AccountVoucherResource::shouldRegisterNavigation(),
        );
        $this->assertTrue(AccountVoucherResource::canViewAny());
        $this->assertTrue(AccountVoucherResource::canView($voucher));
        $this->assertTrue(AccountVoucherResource::canCreate());
        $this->assertTrue(AccountVoucherResource::canEdit($voucher));
        $this->assertTrue(AccountVoucherResource::canDelete($voucher));
        $this->assertTrue(AccountVoucherResource::canDeleteAny());
    }

    public function test_exact_voucher_view_permission_is_read_only(): void
    {
        $user = User::factory()->create([
            'role' => RbacPermissionRegistry::ROLE_MANAGER,
            'is_active' => true,
            'permissions' => [
                'account_vouchers_view',
            ],
        ]);

        $this->actingAs($user);

        $voucher = new AccountVoucher([
            'status' => 'Draft',
        ]);

        $this->assertTrue(AccountVoucherResource::canAccess());
        $this->assertTrue(AccountVoucherResource::canViewAny());
        $this->assertTrue(AccountVoucherResource::canView($voucher));
        $this->assertFalse(AccountVoucherResource::canCreate());
        $this->assertFalse(AccountVoucherResource::canEdit($voucher));
        $this->assertFalse(AccountVoucherResource::canDelete($voucher));
        $this->assertFalse(AccountVoucherResource::canDeleteAny());
    }

    public function test_reports_only_manager_cannot_read_hr_payroll_or_vouchers(): void
    {
        $user = User::factory()->create([
            'role' => RbacPermissionRegistry::ROLE_MANAGER,
            'is_active' => true,
            'permissions' => [
                'reports_view',
            ],
        ]);

        $this->actingAs($user);

        $this->assertFalse(AccountVoucherResource::canViewAny());
        $this->assertFalse(HrReports::canAccess());
        $this->assertFalse(StaffPayrollReport::canAccess());
    }

    public function test_voucher_actions_are_bound_to_resource_authorization(): void
    {
        $listSource = file_get_contents(
            app_path(
                'Filament/Resources/AccountVouchers/Pages/ListAccountVouchers.php',
            ),
        );
        $editSource = file_get_contents(
            app_path(
                'Filament/Resources/AccountVouchers/Pages/EditAccountVoucher.php',
            ),
        );
        $tableSource = file_get_contents(
            app_path(
                'Filament/Resources/AccountVouchers/Tables/AccountVouchersTable.php',
            ),
        );

        $this->assertIsString($listSource);
        $this->assertIsString($editSource);
        $this->assertIsString($tableSource);

        $this->assertStringContainsString(
            'AccountVoucherResource::canCreate()',
            $listSource,
        );
        $this->assertStringContainsString(
            'AccountVoucherResource::canDelete(',
            $editSource,
        );
        $this->assertStringContainsString(
            'AccountVoucherResource::canEdit(',
            $tableSource,
        );
        $this->assertStringContainsString(
            'AccountVoucherResource::canDeleteAny()',
            $tableSource,
        );
    }

    private function proposalUser(string $label): User
    {
        return User::factory()->create([
            'role' => RbacPermissionRegistry::ROLE_MANAGER,
            'is_active' => true,
            'permissions' => ProposalRoleMapping::permissions(
                $label,
            ),
        ]);
    }
}
