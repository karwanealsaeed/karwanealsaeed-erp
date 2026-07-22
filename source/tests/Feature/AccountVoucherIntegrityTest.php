<?php

namespace Tests\Feature;

use App\Services\AccountingPeriodLockService;
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
}