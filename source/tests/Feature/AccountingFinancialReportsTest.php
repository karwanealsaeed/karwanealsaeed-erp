<?php

namespace Tests\Feature;

use App\Filament\Pages\BalanceSheetReport;
use App\Filament\Pages\CashBankBookReport;
use App\Filament\Pages\ProfitLossReport;
use App\Filament\Pages\TrialBalanceReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AccountingTestFixtures;
use Tests\TestCase;

class AccountingFinancialReportsTest extends TestCase
{
    use AccountingTestFixtures;
    use RefreshDatabase;

    public function test_trial_balance_splits_net_debit_and_credit_balances(): void
    {
        $cash =
            $this->createAccountingHead(
                type: 'Asset',
                subType: 'Cash',
                overrides: [
                    'code' => 'A-TB-001',
                    'name' => 'Trial Cash',
                ]
            );

        $income =
            $this->createAccountingHead(
                type: 'Income',
                subType: 'Sales',
                overrides: [
                    'code' => 'I-TB-001',
                    'name' => 'Trial Income',
                ]
            );

        $this->createAccountingLedger(
            account: $cash,
            debit: 750,
            credit: 0
        );

        $this->createAccountingLedger(
            account: $income,
            debit: 0,
            credit: 750
        );

        $rows =
            (new TrialBalanceReport())
                ->rows()
                ->keyBy('code');

        $this->assertEqualsWithDelta(
            750,
            $rows['A-TB-001']['debit'],
            0.01
        );

        $this->assertEqualsWithDelta(
            0,
            $rows['A-TB-001']['credit'],
            0.01
        );

        $this->assertEqualsWithDelta(
            0,
            $rows['I-TB-001']['debit'],
            0.01
        );

        $this->assertEqualsWithDelta(
            750,
            $rows['I-TB-001']['credit'],
            0.01
        );

        $this->assertEqualsWithDelta(
            $rows->sum('debit'),
            $rows->sum('credit'),
            0.01
        );
    }

    public function test_profit_and_loss_applies_date_filter_and_natural_balances(): void
    {
        $income =
            $this->createAccountingHead(
                type: 'Income',
                subType: 'Sales',
                overrides: [
                    'code' => 'I-PL-001',
                    'name' => 'Profit Income',
                ]
            );

        $expense =
            $this->createAccountingHead(
                type: 'Expense',
                subType: 'Expense',
                overrides: [
                    'code' => 'E-PL-001',
                    'name' => 'Profit Expense',
                ]
            );

        $this->createAccountingLedger(
            account: $income,
            debit: 0,
            credit: 500,
            entryDate: '2026-04-10'
        );

        $this->createAccountingLedger(
            account: $income,
            debit: 0,
            credit: 999,
            entryDate: '2025-12-31'
        );

        $this->createAccountingLedger(
            account: $expense,
            debit: 200,
            credit: 0,
            entryDate: '2026-04-12'
        );

        $this->setAccountingReportQuery([
            'from_date' => '2026-04-01',
            'to_date' => '2026-04-30',
        ]);

        $page = new ProfitLossReport();

        $incomeRows =
            $page->incomeRows()
                ->keyBy('code');

        $expenseRows =
            $page->expenseRows()
                ->keyBy('code');

        $this->assertEqualsWithDelta(
            500,
            $incomeRows['I-PL-001']['amount'],
            0.01
        );

        $this->assertEqualsWithDelta(
            200,
            $expenseRows['E-PL-001']['amount'],
            0.01
        );

        $this->assertEqualsWithDelta(
            300,
            $incomeRows->sum('amount')
                - $expenseRows->sum('amount'),
            0.01
        );
    }

    public function test_balance_sheet_returns_asset_liability_and_equity_balances(): void
    {
        $asset =
            $this->createAccountingHead(
                type: 'Asset',
                subType: 'Cash',
                overrides: [
                    'code' => 'A-BS-001',
                    'name' => 'Balance Asset',
                    'opening_debit' => 100,
                ]
            );

        $liability =
            $this->createAccountingHead(
                type: 'Liability',
                subType: 'Supplier',
                overrides: [
                    'code' => 'L-BS-001',
                    'name' => 'Balance Liability',
                    'opening_credit' => 50,
                ]
            );

        $equity =
            $this->createAccountingHead(
                type: 'Equity',
                subType: null,
                overrides: [
                    'code' => 'Q-BS-001',
                    'name' => 'Balance Equity',
                    'opening_credit' => 25,
                ]
            );

        $this->createAccountingLedger(
            account: $asset,
            debit: 900,
            credit: 0,
            entryDate: '2026-05-10'
        );

        $this->createAccountingLedger(
            account: $liability,
            debit: 0,
            credit: 350,
            entryDate: '2026-05-10'
        );

        $this->createAccountingLedger(
            account: $equity,
            debit: 0,
            credit: 275,
            entryDate: '2026-05-10'
        );

        $this->setAccountingReportQuery([
            'to_date' => '2026-05-31',
        ]);

        $page = new BalanceSheetReport();

        $assets =
            $page->assetRows()
                ->keyBy('code');

        $liabilities =
            $page->liabilityRows()
                ->keyBy('code');

        $equities =
            $page->equityRows()
                ->keyBy('code');

        $this->assertEqualsWithDelta(
            1000,
            $assets['A-BS-001']['amount'],
            0.01
        );

        $this->assertEqualsWithDelta(
            400,
            $liabilities['L-BS-001']['amount'],
            0.01
        );

        $this->assertEqualsWithDelta(
            300,
            $equities['Q-BS-001']['amount'],
            0.01
        );
    }

    public function test_cash_bank_book_filters_account_and_calculates_running_balance(): void
    {
        $cash =
            $this->createAccountingHead(
                type: 'Asset',
                subType: 'Cash',
                overrides: [
                    'code' => 'A-CB-001',
                    'name' => 'Cash Book Account',
                ]
            );

        $otherBank =
            $this->createAccountingHead(
                type: 'Asset',
                subType: 'Bank',
                overrides: [
                    'code' => 'A-CB-002',
                    'name' => 'Other Bank Account',
                ]
            );

        $this->createAccountingLedger(
            account: $cash,
            debit: 300,
            credit: 0,
            entryDate: '2026-06-01'
        );

        $this->createAccountingLedger(
            account: $cash,
            debit: 0,
            credit: 80,
            entryDate: '2026-06-02'
        );

        $this->createAccountingLedger(
            account: $otherBank,
            debit: 999,
            credit: 0,
            entryDate: '2026-06-02'
        );

        $this->setAccountingReportQuery([
            'account_id' => $cash->id,
            'from_date' => '2026-06-01',
            'to_date' => '2026-06-30',
        ]);

        $rows =
            (new CashBankBookReport())
                ->rows()
                ->values();

        $this->assertCount(2, $rows);

        $this->assertEqualsWithDelta(
            300,
            $rows[0]['balance'],
            0.01
        );

        $this->assertEqualsWithDelta(
            220,
            $rows[1]['balance'],
            0.01
        );

        $this->assertSame(
            [
                'Cash Book Account',
                'Cash Book Account',
            ],
            $rows->pluck('account')
                ->map(
                    static fn (string $display): string =>
                        str_contains($display, ' - ')
                            ? trim(
                                (string) substr(
                                    $display,
                                    strpos($display, ' - ') + 3
                                )
                            )
                            : $display
                )
                ->all()
        );
    }
}