<?php

namespace Tests\Feature;

use App\Filament\Resources\AccountVouchers\Pages\CreateAccountVoucher;
use App\Models\AccountLedger;
use App\Models\AccountVoucher;
use App\Services\AccountingPeriodLockService;
use App\Services\AccountVoucherPostingValidator;
use ReflectionClass;
use ReflectionMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\Support\AccountingTestFixtures;
use Tests\TestCase;

class AccountVoucherIntegrityTest extends TestCase
{
    use AccountingTestFixtures;
    use RefreshDatabase;

    public function test_posted_voucher_and_lines_are_immutable_in_locked_period(): void
    {
        $debitAccount =
            $this->createAccountingHead(
                type: 'Asset',
                subType: 'Cash'
            );

        $creditAccount =
            $this->createAccountingHead(
                type: 'Income',
                subType: 'Sales'
            );

        $voucher =
            $this->createPostedAccountingVoucher(
                debitAccount: $debitAccount,
                creditAccount: $creditAccount,
                amount: 500,
                voucherDate: '2026-01-15'
            );

        $this->insertAccountingRow(
            'accounting_period_locks',
            [
                'name' => 'January 2026 Test Lock',
                'period_start' => '2026-01-01',
                'period_end' => '2026-01-31',
                'lock_scope' =>
                    AccountingPeriodLockService::SCOPE_GENERAL_LEDGER,
                'status' => 'Locked',
                'created_by' => null,
                'locked_by' => null,
                'locked_at' => now(),
                'notes' => 'Regression fixture',
            ]
        );

        $this->assertFalse(
            $voucher->refresh()
                ->canBeManuallyChanged()
        );

        $voucherBlocked = false;

        try {
            $voucher->refresh()->update([
                'description' =>
                    'This update must remain blocked',
            ]);
        } catch (ValidationException) {
            $voucherBlocked = true;
        }

        $lineBlocked = false;

        try {
            $voucher->lines()
                ->orderBy('sort_order')
                ->firstOrFail()
                ->update([
                    'description' =>
                        'This line update must remain blocked',
                ]);
        } catch (ValidationException) {
            $lineBlocked = true;
        }

        $this->assertTrue($voucherBlocked);
        $this->assertTrue($lineBlocked);

        $this->assertSame(
            'Accounting regression fixture',
            (string) DB::table('account_vouchers')
                ->where('id', $voucher->id)
                ->value('description')
        );

        $this->assertSame(
            'Debit fixture line',
            (string) DB::table('account_voucher_lines')
                ->where('account_voucher_id', $voucher->id)
                ->orderBy('sort_order')
                ->value('description')
        );
    }
    public function test_manual_voucher_creation_is_forced_to_draft(): void
    {
        $page =
            (new ReflectionClass(
                CreateAccountVoucher::class
            ))->newInstanceWithoutConstructor();

        $method =
            new ReflectionMethod(
                CreateAccountVoucher::class,
                'mutateFormDataBeforeCreate'
            );

        $data =
            $method->invoke(
                $page,
                [
                    'status' => 'Posted',
                    'reference_no' => 'TEST-DIRECT-POST',
                ]
            );

        $this->assertSame(
            'Draft',
            $data['status']
        );
    }

    public function test_unbalanced_posted_voucher_never_reaches_general_ledger(): void
    {
        $debitAccount =
            $this->createAccountingHead(
                type: 'Asset',
                subType: 'Cash'
            );

        $creditAccount =
            $this->createAccountingHead(
                type: 'Income',
                subType: 'Sales'
            );

        $voucher =
            AccountVoucher::query()->create([
                'voucher_date' => '2026-02-10',
                'voucher_type' => 'Journal',
                'reference_no' =>
                    'TEST-UNBALANCED-POSTED',
                'description' =>
                    'Must never reach General Ledger',
                'total_debit' => 0,
                'total_credit' => 0,
                'status' => 'Posted',
            ]);

        $voucher->lines()->create([
            'account_head_id' =>
                $debitAccount->id,
            'debit' => 1000,
            'credit' => 0,
            'sort_order' => 1,
        ]);

        $voucher->lines()->create([
            'account_head_id' =>
                $creditAccount->id,
            'debit' => 0,
            'credit' => 900,
            'sort_order' => 2,
        ]);

        $voucher->refreshTotals();
        $voucher->syncLedgerEntries();
        $voucher->refresh();

        $this->assertFalse(
            $voucher->is_balanced
        );

        $this->assertSame(
            0,
            AccountLedger::query()
                ->where(
                    'account_voucher_id',
                    $voucher->id
                )
                ->count()
        );
    }

    public function test_posting_validator_rejects_unequal_debit_and_credit_totals(): void
    {
        $validator =
            app(
                AccountVoucherPostingValidator::class
            );

        $this->expectException(
            ValidationException::class
        );

        $validator->assertCanPost([
            [
                'account_head_id' => 1,
                'debit' => 1000,
                'credit' => 0,
            ],
            [
                'account_head_id' => 2,
                'debit' => 0,
                'credit' => 900,
            ],
        ]);
    }

    public function test_posting_validator_accepts_valid_double_entry_lines(): void
    {
        $validator =
            app(
                AccountVoucherPostingValidator::class
            );

        $validator->assertCanPost([
            [
                'account_head_id' => 1,
                'debit' => 1000,
                'credit' => 0,
            ],
            [
                'account_head_id' => 2,
                'debit' => 0,
                'credit' => 1000,
            ],
        ]);

        $this->assertTrue(true);
    }
}