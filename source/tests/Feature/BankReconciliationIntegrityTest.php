<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\BankStatementEntry;
use App\Services\BankReconciliationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AccountingTestFixtures;
use Tests\TestCase;

class BankReconciliationIntegrityTest extends TestCase
{
    use AccountingTestFixtures;
    use RefreshDatabase;

    public function test_matched_statement_and_book_entry_reconcile_to_zero_difference(): void
    {
        $bankHead =
            $this->createAccountingHead(
                type: 'Asset',
                subType: 'Bank',
                overrides: [
                    'code' => 'A-BANK-REG-001',
                    'name' => 'Regression Bank Head',
                ]
            );

        $incomeHead =
            $this->createAccountingHead(
                type: 'Income',
                subType: 'Sales',
                overrides: [
                    'code' => 'I-BANK-REG-001',
                    'name' => 'Regression Bank Income',
                ]
            );

        $bankAccount =
            BankAccount::query()->create([
                'account_head_id' => $bankHead->id,
                'name' => 'Regression Bank',
                'bank_name' => 'Regression Bank Limited',
                'account_title' => 'Regression Account',
                'account_number' => 'REG-001',
                'iban' => null,
                'branch' => null,
                'currency' => 'PKR',
                'opening_date' => '2026-03-01',
                'opening_balance' => 0,
                'status' => 'Active',
                'notes' => null,
            ]);

        $voucher =
            $this->createPostedAccountingVoucher(
                debitAccount: $bankHead,
                creditAccount: $incomeHead,
                amount: 500,
                voucherDate: '2026-03-10'
            );

        $ledger =
            $voucher->ledgers()
                ->where('account_head_id', $bankHead->id)
                ->firstOrFail();

        BankStatementEntry::query()->create([
            'entry_no' => 'BSE-REG-001',
            'bank_account_id' => $bankAccount->id,
            'statement_date' => '2026-03-10',
            'reference_no' => $voucher->voucher_no,
            'cheque_no' => null,
            'description' => 'Regression matched deposit',
            'deposit' => 500,
            'withdrawal' => 0,
            'running_balance' => 500,
            'reconciliation_status' => 'Matched',
            'matched_account_ledger_id' => $ledger->id,
            'matched_by' => null,
            'matched_at' => now(),
            'created_by' => null,
            'notes' => null,
        ]);

        $report =
            app(
                BankReconciliationService::class
            )->generate(
                bankAccountId: $bankAccount->id,
                dateFrom: '2026-03-01',
                dateTo: '2026-03-31'
            );

        $this->assertSame(
            $bankAccount->id,
            $report['bank_account']['id']
        );

        $this->assertEqualsWithDelta(
            500,
            $report['stats']['statement_closing'],
            0.01
        );

        $this->assertEqualsWithDelta(
            500,
            $report['stats']['book_closing'],
            0.01
        );

        $this->assertEqualsWithDelta(
            0,
            $report['stats']['difference'],
            0.01
        );

        $this->assertSame(
            1,
            $report['stats']['matched_count']
        );

        $this->assertSame(
            0,
            $report['stats']['unmatched_statement_count']
        );

        $this->assertSame(
            0,
            $report['stats']['unmatched_book_count']
        );
    }
}