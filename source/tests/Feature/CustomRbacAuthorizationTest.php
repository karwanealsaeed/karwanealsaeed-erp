<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Concerns\HasRbacPageAuthorization;
use App\Filament\Concerns\HasRbacResourceAuthorization;
use App\Filament\Pages\AccountingApprovalCenter;
use App\Filament\Pages\AccountingPostingAudit;
use App\Filament\Pages\BankReconciliation;
use App\Filament\Pages\CustomerAgingReport;
use App\Filament\Pages\HrReports;
use App\Filament\Pages\SupplierAgingReport;
use App\Filament\Resources\BankAccounts\BankAccountResource;
use App\Filament\Resources\Departments\DepartmentResource;
use App\Filament\Resources\Designations\DesignationResource;
use App\Filament\Resources\LeaveTypes\LeaveTypeResource;
use App\Filament\Resources\Staff\StaffResource;
use App\Filament\Resources\StaffAttendances\StaffAttendanceResource;
use App\Filament\Resources\StaffMembers\StaffMemberResource;
use App\Filament\Resources\StaffPayrolls\StaffPayrollResource;
use App\Filament\Resources\StaffDocuments\StaffDocumentResource;
use App\Filament\Resources\StaffLeaves\StaffLeaveResource;
use App\Filament\Resources\StaffLedgerEntries\StaffLedgerEntryResource;
use App\Filament\Resources\StaffPerformanceNotes\StaffPerformanceNoteResource;
use App\Filament\Resources\SupplierBills\SupplierBillResource;
use App\Filament\Resources\SupplierPayments\SupplierPaymentResource;
use App\Filament\Resources\Suppliers\SupplierResource;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Models\User;
use App\Support\RbacPermissionRegistry;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomRbacAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_permission_normalization_accepts_dot_and_underscore_forms(): void
    {
        $user =
            User::factory()->create(
                [
                    'role' =>
                        RbacPermissionRegistry::ROLE_ACCOUNTANT,
                    'is_active' =>
                        true,
                    'permissions' => [
                        'reports_view',
                        'flight.payments.edit',
                    ],
                ],
            );

        $this->assertTrue(
            $user->hasPermission(
                'reports.view',
            ),
        );

        $this->assertTrue(
            $user->hasPermission(
                'reports_view',
            ),
        );

        $this->assertTrue(
            $user->hasPermission(
                'flight_payments_edit',
            ),
        );

        $this->assertFalse(
            $user->hasPermission(
                'users.manage',
            ),
        );
    }

    public function test_admin_panel_entry_is_active_role_and_permission_aware(): void
    {
        $panel =
            Filament::getPanel(
                'admin',
            );

        $admin =
            User::factory()->create(
                [
                    'role' =>
                        RbacPermissionRegistry::ROLE_ADMIN,
                    'is_active' =>
                        true,
                    'permissions' =>
                        [],
                ],
            );

        $accountant =
            User::factory()->create(
                [
                    'role' =>
                        RbacPermissionRegistry::ROLE_ACCOUNTANT,
                    'is_active' =>
                        true,
                    'permissions' => [
                        'reports_view',
                    ],
                ],
            );

        $salesStaff =
            User::factory()->create(
                [
                    'role' =>
                        RbacPermissionRegistry::ROLE_SALES_STAFF,
                    'is_active' =>
                        true,
                    'permissions' => [
                        'customers_view',
                    ],
                ],
            );

        $managerWithAiOnly =
            User::factory()->create(
                [
                    'role' =>
                        RbacPermissionRegistry::ROLE_MANAGER,
                    'is_active' =>
                        true,
                    'permissions' => [
                        'ai_settings_view',
                    ],
                ],
            );

        $managerWithVisaOnly =
            User::factory()->create(
                [
                    'role' =>
                        RbacPermissionRegistry::ROLE_MANAGER,
                    'is_active' =>
                        true,
                    'permissions' => [
                        'visa_applications_view',
                    ],
                ],
            );

        $emptyPermissions =
            User::factory()->create(
                [
                    'role' =>
                        RbacPermissionRegistry::ROLE_ACCOUNTANT,
                    'is_active' =>
                        true,
                    'permissions' =>
                        [],
                ],
            );

        $unknownRole =
            new User(
                [
                    'role' =>
                        'Unknown Role',
                    'is_active' =>
                        true,
                    'permissions' => [
                        'reports_view',
                    ],
                ],
            );

        $inactiveAdmin =
            User::factory()->create(
                [
                    'role' =>
                        RbacPermissionRegistry::ROLE_ADMIN,
                    'is_active' =>
                        false,
                    'permissions' =>
                        [],
                ],
            );

        $this->assertTrue(
            $admin->canAccessPanel(
                $panel,
            ),
        );

        $this->assertTrue(
            $accountant->canAccessPanel(
                $panel,
            ),
        );

        $this->assertTrue(
            $salesStaff->canAccessPanel(
                $panel,
            ),
        );

        $this->assertTrue(
            $managerWithAiOnly->canAccessPanel(
                $panel,
            ),
        );

        $this->assertTrue(
            $managerWithVisaOnly->canAccessPanel(
                $panel,
            ),
        );

        $this->assertFalse(
            $emptyPermissions->canAccessPanel(
                $panel,
            ),
        );

        $this->assertFalse(
            $unknownRole->canAccessPanel(
                $panel,
            ),
        );

        $this->assertFalse(
            $inactiveAdmin->canAccessPanel(
                $panel,
            ),
        );
    }

    public function test_page_matrix_enforces_role_and_permission_together(): void
    {
        $accountant =
            User::factory()->create(
                [
                    'role' =>
                        RbacPermissionRegistry::ROLE_ACCOUNTANT,
                    'is_active' =>
                        true,
                    'permissions' => [
                        'reports_view',
                    ],
                ],
            );

        $this->actingAs(
            $accountant,
        );

        $this->assertTrue(
            CustomerAgingReport::canAccess(),
        );

        $this->assertTrue(
            SupplierAgingReport::canAccess(),
        );

        $this->assertTrue(
            AccountingPostingAudit::canAccess(),
        );

        $this->assertFalse(
            BankReconciliation::canAccess(),
        );

        $this->assertFalse(
            AccountingApprovalCenter::canAccess(),
        );

        $this->assertFalse(
            HrReports::canAccess(),
        );

        $accountant->permissions = [
            'reports_view',
            'visa_payments_edit',
        ];

        $accountant->save();

        $this->assertTrue(
            BankReconciliation::canAccess(),
        );

        $this->assertTrue(
            AccountingApprovalCenter::canAccess(),
        );
    }

    public function test_resource_matrix_keeps_sales_supplier_access_read_only(): void
    {
        $salesStaff =
            User::factory()->create(
                [
                    'role' =>
                        RbacPermissionRegistry::ROLE_SALES_STAFF,
                    'is_active' =>
                        true,
                    'permissions' => [
                        'suppliers_view',
                    ],
                ],
            );

        $this->actingAs(
            $salesStaff,
        );

        $this->assertTrue(
            SupplierResource::canViewAny(),
        );

        $this->assertFalse(
            SupplierResource::canCreate(),
        );

        $this->assertFalse(
            BankAccountResource::canViewAny(),
        );

        $this->assertFalse(
            SupplierBillResource::canViewAny(),
        );

        $this->assertFalse(
            StaffResource::canViewAny(),
        );
    }

    public function test_manager_and_accountant_sensitive_resource_matrix(): void
    {
        $manager =
            User::factory()->create(
                [
                    'role' =>
                        RbacPermissionRegistry::ROLE_MANAGER,
                    'is_active' =>
                        true,
                    'permissions' => [
                        'staff_view',
                        'staff_create',
                        RbacPermissionRegistry::ACCOUNTING_MANAGE,
                    ],
                ],
            );

        $this->actingAs(
            $manager,
        );

        $this->assertTrue(
            StaffResource::canViewAny(),
        );

        $this->assertTrue(
            StaffResource::canCreate(),
        );

        $this->assertTrue(
            BankAccountResource::canViewAny(),
        );

        $this->assertTrue(
            SupplierPaymentResource::canCreate(),
        );

        $accountant =
            User::factory()->create(
                [
                    'role' =>
                        RbacPermissionRegistry::ROLE_ACCOUNTANT,
                    'is_active' =>
                        true,
                    'permissions' => [
                        RbacPermissionRegistry::ACCOUNTING_MANAGE,
                    ],
                ],
            );

        $this->actingAs(
            $accountant,
        );

        $this->assertTrue(
            BankAccountResource::canViewAny(),
        );

        $this->assertTrue(
            SupplierBillResource::canCreate(),
        );

        $this->assertFalse(
            StaffResource::canViewAny(),
        );
    }

    public function test_all_audited_classes_use_central_authorization_traits(): void
    {
        foreach (
            [
                AccountingApprovalCenter::class,
                AccountingPostingAudit::class,
                BankReconciliation::class,
                CustomerAgingReport::class,
                HrReports::class,
                SupplierAgingReport::class,
            ]
            as $pageClass
        ) {
            $this->assertContains(
                HasRbacPageAuthorization::class,
                class_uses_recursive(
                    $pageClass,
                ),
            );
        }

        foreach (
            [
                BankAccountResource::class,
                DepartmentResource::class,
                DesignationResource::class,
                LeaveTypeResource::class,
                StaffResource::class,
                StaffAttendanceResource::class,
                StaffMemberResource::class,
                StaffPayrollResource::class,
                StaffDocumentResource::class,
                StaffLeaveResource::class,
                StaffLedgerEntryResource::class,
                StaffPerformanceNoteResource::class,
                SupplierBillResource::class,
                SupplierPaymentResource::class,
                SupplierResource::class,
            ]
            as $resourceClass
        ) {
            $this->assertContains(
                HasRbacResourceAuthorization::class,
                class_uses_recursive(
                    $resourceClass,
                ),
            );
        }

        $this->assertCount(
            7,
            RbacPermissionRegistry::pageClasses(),
        );

        $this->assertCount(
            39,
            RbacPermissionRegistry::resourceClasses(),
        );
    }

    public function test_role_catalog_and_presets_include_accounting_control(): void
    {
        $this->assertSame(
            [
                'Admin' =>
                    'Admin',
                'Manager' =>
                    'Manager',
                'Accountant' =>
                    'Accountant',
                'Sales Staff' =>
                    'Sales Staff',
            ],
            RbacPermissionRegistry::roleOptions(),
        );

        $this->assertArrayHasKey(
            RbacPermissionRegistry::ACCOUNTING_MANAGE,
            UserForm::getPermissionOptions(),
        );

        $this->assertContains(
            RbacPermissionRegistry::ACCOUNTING_MANAGE,
            UserForm::getDefaultPermissions(
                RbacPermissionRegistry::ROLE_MANAGER,
            ),
        );

        $this->assertContains(
            RbacPermissionRegistry::ACCOUNTING_MANAGE,
            UserForm::getDefaultPermissions(
                RbacPermissionRegistry::ROLE_ACCOUNTANT,
            ),
        );

        $this->assertNotContains(
            RbacPermissionRegistry::ACCOUNTING_MANAGE,
            UserForm::getDefaultPermissions(
                RbacPermissionRegistry::ROLE_SALES_STAFF,
            ),
        );
    }
}
