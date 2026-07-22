<?php

namespace Tests\Support;

use App\Models\AccountHead;
use App\Models\AccountLedger;
use App\Models\AccountVoucher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait AccountingTestFixtures
{
    private static int $accountingFixtureSequence = 0;

    protected function accountingToken(
        string $prefix
    ): string {
        self::$accountingFixtureSequence++;

        return strtoupper($prefix)
            . '-'
            .str_pad(
                (string) self::$accountingFixtureSequence,
                5,
                '0',
                STR_PAD_LEFT
            );
    }

    protected function insertAccountingRow(
        string $table,
        array $values
    ): int {
        $columns = Schema::getColumnListing($table);

        $filtered = array_intersect_key(
            $values,
            array_flip($columns)
        );

        if (in_array('created_at', $columns, true)) {
            $filtered['created_at'] =
                $filtered['created_at']
                ?? now();
        }

        if (in_array('updated_at', $columns, true)) {
            $filtered['updated_at'] =
                $filtered['updated_at']
                ?? now();
        }

        return (int) DB::table($table)
            ->insertGetId($filtered);
    }

    protected function createAccountingHead(
        string $type,
        ?string $subType = null,
        array $overrides = []
    ): AccountHead {
        $token = $this->accountingToken('ACC');

        return AccountHead::query()->create(
            array_merge(
                [
                    'code' => $token,
                    'name' => 'Test '.$token,
                    'type' => $type,
                    'sub_type' => $subType,
                    'parent_id' => null,
                    'opening_debit' => 0,
                    'opening_credit' => 0,
                    'status' => 'Active',
                    'notes' => null,
                ],
                $overrides
            )
        );
    }

    protected function createAccountingLedger(
        AccountHead $account,
        float $debit,
        float $credit,
        string $entryDate = '2026-01-15',
        array $overrides = []
    ): AccountLedger {
        $token = $this->accountingToken('GL');

        return AccountLedger::query()->create(
            array_merge(
                [
                    'account_head_id' => $account->id,
                    'account_voucher_id' => null,
                    'account_voucher_line_id' => null,
                    'entry_date' => $entryDate,
                    'voucher_no' => $token,
                    'voucher_type' => 'Journal',
                    'reference_no' => $token,
                    'description' => 'Accounting regression fixture',
                    'debit' => $debit,
                    'credit' => $credit,
                    'status' => 'Posted',
                ],
                $overrides
            )
        );
    }

    protected function createPostedAccountingVoucher(
        AccountHead $debitAccount,
        AccountHead $creditAccount,
        float $amount = 100,
        string $voucherDate = '2026-01-15'
    ): AccountVoucher {
        $token = $this->accountingToken('JV');

        $voucher = AccountVoucher::query()->create([
            'voucher_no' => $token,
            'voucher_date' => $voucherDate,
            'voucher_type' => 'Journal',
            'reference_no' => $token,
            'party_name' => 'Regression Fixture',
            'description' => 'Accounting regression fixture',
            'total_debit' => 0,
            'total_credit' => 0,
            'status' => 'Draft',
            'created_by' => null,
            'source_model' => null,
            'source_id' => null,
            'source_type' => null,
            'source_no' => null,
        ]);

        $voucher->lines()->create([
            'account_head_id' => $debitAccount->id,
            'description' => 'Debit fixture line',
            'debit' => $amount,
            'credit' => 0,
            'sort_order' => 1,
        ]);

        $voucher->lines()->create([
            'account_head_id' => $creditAccount->id,
            'description' => 'Credit fixture line',
            'debit' => 0,
            'credit' => $amount,
            'sort_order' => 2,
        ]);

        $voucher->update([
            'status' => 'Posted',
        ]);

        $voucher->refreshTotals();
        $voucher->syncLedgerEntries();

        return $voucher->refresh();
    }

    protected function setAccountingReportQuery(
        array $query
    ): void {
        request()->query->replace($query);
    }
}