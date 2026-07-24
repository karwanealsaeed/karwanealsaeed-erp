<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Concerns\HasRbacResourceAuthorization;
use App\Filament\Pages\AccountingApprovalCenter;
use App\Filament\Pages\AccountingPostingAudit;
use App\Filament\Pages\BankReconciliation;
use App\Filament\Resources\BankStatementEntries\BankStatementEntryResource;
use App\Filament\Resources\FlightBookings\FlightBookingResource;
use App\Models\BankStatementEntry;
use App\Models\FlightBooking;
use App\Models\User;
use App\Support\ProposalRoleMapping;
use App\Support\RbacPermissionRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketingManagerFlightDeleteAndBankStatementRbacTest extends TestCase
{
    use RefreshDatabase;

    public function test_bank_statement_uses_central_rbac_contract(): void
    {
        $this->assertContains(
            HasRbacResourceAuthorization::class,
            class_uses_recursive(BankStatementEntryResource::class),
        );

        $this->assertContains(
            BankStatementEntryResource::class,
            RbacPermissionRegistry::resourceClasses(),
        );
    }

    public function test_ticketing_manager_keeps_approved_operational_access_without_delete_rights(): void
    {
        $ticketing = User::factory()->create([
            'role' => RbacPermissionRegistry::ROLE_MANAGER,
            'is_active' => true,
            'permissions' => ProposalRoleMapping::permissions(
                ProposalRoleMapping::TICKETING_MANAGER,
            ),
        ]);

        $this->actingAs($ticketing);

        $flight = new FlightBooking;
        $bankStatement = new BankStatementEntry;

        $this->assertTrue(FlightBookingResource::canViewAny());
        $this->assertTrue(FlightBookingResource::canCreate());
        $this->assertTrue(FlightBookingResource::canEdit($flight));
        $this->assertFalse(FlightBookingResource::canDelete($flight));
        $this->assertFalse(FlightBookingResource::canDeleteAny());

        $this->assertTrue(BankStatementEntryResource::canViewAny());
        $this->assertTrue(BankStatementEntryResource::canCreate());
        $this->assertTrue(
            BankStatementEntryResource::canEdit($bankStatement),
        );
        $this->assertFalse(
            BankStatementEntryResource::canDelete($bankStatement),
        );

        $this->assertTrue(BankReconciliation::canAccess());
        $this->assertTrue(AccountingApprovalCenter::canAccess());
        $this->assertTrue(AccountingPostingAudit::canAccess());
    }

    public function test_reports_only_manager_has_read_only_bank_statement_access(): void
    {
        $manager = User::factory()->create([
            'role' => RbacPermissionRegistry::ROLE_MANAGER,
            'is_active' => true,
            'permissions' => [
                'reports_view',
            ],
        ]);

        $this->actingAs($manager);

        $bankStatement = new BankStatementEntry;

        $this->assertTrue(BankStatementEntryResource::canViewAny());
        $this->assertFalse(BankStatementEntryResource::canCreate());
        $this->assertFalse(
            BankStatementEntryResource::canEdit($bankStatement),
        );
        $this->assertFalse(
            BankStatementEntryResource::canDelete($bankStatement),
        );

        $this->assertTrue(AccountingPostingAudit::canAccess());
        $this->assertFalse(BankReconciliation::canAccess());
        $this->assertFalse(AccountingApprovalCenter::canAccess());
    }

    public function test_unrelated_manager_and_sales_staff_cannot_access_bank_statement(): void
    {
        foreach (
            [
                RbacPermissionRegistry::ROLE_MANAGER,
                RbacPermissionRegistry::ROLE_SALES_STAFF,
            ] as $role
        ) {
            $user = User::factory()->create([
                'role' => $role,
                'is_active' => true,
                'permissions' => [],
            ]);

            $this->actingAs($user);

            $bankStatement = new BankStatementEntry;

            $this->assertFalse(BankStatementEntryResource::canViewAny());
            $this->assertFalse(BankStatementEntryResource::canCreate());
            $this->assertFalse(
                BankStatementEntryResource::canEdit($bankStatement),
            );
            $this->assertFalse(
                BankStatementEntryResource::canDelete($bankStatement),
            );
        }
    }

    public function test_accounting_control_user_keeps_full_bank_statement_access(): void
    {
        $accountant = User::factory()->create([
            'role' => RbacPermissionRegistry::ROLE_ACCOUNTANT,
            'is_active' => true,
            'permissions' => [
                RbacPermissionRegistry::ACCOUNTING_MANAGE,
            ],
        ]);

        $this->actingAs($accountant);

        $bankStatement = new BankStatementEntry;

        $this->assertTrue(BankStatementEntryResource::canViewAny());
        $this->assertTrue(BankStatementEntryResource::canCreate());
        $this->assertTrue(
            BankStatementEntryResource::canEdit($bankStatement),
        );
        $this->assertTrue(
            BankStatementEntryResource::canDelete($bankStatement),
        );
    }

    public function test_flight_delete_header_action_is_bound_to_resource_authorization(): void
    {
        $source = file_get_contents(
            app_path(
                'Filament/Resources/FlightBookings/Pages/EditFlightBooking.php',
            ),
        );

        $this->assertIsString($source);
        $this->assertStringContainsString(
            'DeleteAction::make()->hidden(fn (): bool => ! FlightBookingResource::canDelete($this->record))',
            $source,
        );
    }
}
