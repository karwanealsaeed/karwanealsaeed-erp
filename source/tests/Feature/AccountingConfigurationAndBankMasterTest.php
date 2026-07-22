<?php

namespace Tests\Feature;

use App\Filament\Pages\BankReconciliation;
use App\Models\BankAccount;
use App\Services\AccountingPeriodLockService;
use App\Services\BankAccountMasterValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Support\AccountingTestFixtures;
use Tests\TestCase;

class AccountingConfigurationAndBankMasterTest extends TestCase
{
    use AccountingTestFixtures;
    use RefreshDatabase;

    public function test_period_lock_form_uses_service_scope_options(): void
    {
        $source =
            file_get_contents(
                app_path(
                    'Filament/Resources/AccountingPeriodLocks/Schemas/AccountingPeriodLockForm.php'
                )
            );

        $this->assertIsString($source);

        $this->assertStringContainsString(
            'AccountingPeriodLockService::scopeOptions()',
            $source
        );

        $this->assertArrayHasKey(
            AccountingPeriodLockService::SCOPE_GENERAL_LEDGER,
            AccountingPeriodLockService::scopeOptions()
        );
    }

    public function test_bank_account_requires_active_bank_gl_head(): void
    {
        $nonBankHead =
            $this->createAccountingHead(
                type: 'Asset',
                subType: 'Cash'
            );

        $this->expectException(
            ValidationException::class
        );

        BankAccount::query()->create([
            'account_head_id' => $nonBankHead->id,
            'name' => 'Invalid Linked Bank',
            'bank_name' => 'Regression Bank',
            'account_title' => 'Regression',
            'account_number' => 'INVALID-001',
            'iban' => null,
            'branch' => null,
            'currency' => 'PKR',
            'opening_date' => '2026-04-01',
            'opening_balance' => 0,
            'status' => 'Active',
            'notes' => null,
        ]);
    }

    public function test_valid_active_bank_account_populates_reconciliation_selector(): void
    {
        $bankHead =
            $this->createAccountingHead(
                type: 'Asset',
                subType: 'Bank'
            );

        app(
            BankAccountMasterValidator::class
        )->assertValidAccountHead(
            $bankHead->id
        );

        $bankAccount =
            BankAccount::query()->create([
                'account_head_id' => $bankHead->id,
                'name' => 'Regression Bank Account',
                'bank_name' => 'Regression Bank Limited',
                'account_title' => 'Regression Account',
                'account_number' => 'REG-BANK-001',
                'iban' => null,
                'branch' => null,
                'currency' => 'PKR',
                'opening_date' => '2026-04-01',
                'opening_balance' => 0,
                'status' => 'Active',
                'notes' => null,
            ]);

        $page = new BankReconciliation();
        $page->mount();

        $this->assertSame(
            $bankAccount->id,
            (int) $page->bankAccountId
        );
    }
}