<?php

namespace App\Services;

use App\Models\AccountLedger;
use App\Models\BankAccount;
use App\Models\BankStatementEntry;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class BankReconciliationService
{
    public function generate(
        int|string|null $bankAccountId,
        ?string $dateFrom,
        ?string $dateTo,
    ): array {
        $from = filled($dateFrom)
            ? Carbon::parse($dateFrom)->startOfDay()
            : now()->startOfMonth();

        $to = filled($dateTo)
            ? Carbon::parse($dateTo)->endOfDay()
            : now()->endOfMonth();

        if ($to->lessThan($from)) {
            [$from, $to] = [
                $to->copy()->startOfDay(),
                $from->copy()->endOfDay(),
            ];
        }

        if (
            ! $bankAccountId
            || ! Schema::hasTable('bank_accounts')
            || ! Schema::hasTable(
                'bank_statement_entries'
            )
        ) {
            return $this->emptyReport(
                $from,
                $to
            );
        }

        $bankAccount = BankAccount::query()
            ->with('accountHead')
            ->find($bankAccountId);

        if (! $bankAccount) {
            return $this->emptyReport(
                $from,
                $to
            );
        }

        $periodStatementEntries =
            BankStatementEntry::query()
                ->with([
                    'matchedLedger',
                    'matcher',
                ])
                ->where(
                    'bank_account_id',
                    $bankAccount->id
                )
                ->whereBetween(
                    'statement_date',
                    [
                        $from->format('Y-m-d'),
                        $to->format('Y-m-d'),
                    ]
                )
                ->orderBy('statement_date')
                ->orderBy('id')
                ->get();

        $periodLedgers = AccountLedger::query()
            ->where(
                'account_head_id',
                $bankAccount->account_head_id
            )
            ->where('status', 'Posted')
            ->whereBetween('entry_date', [
                $from->format('Y-m-d'),
                $to->format('Y-m-d'),
            ])
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get();

        $matchedLedgerIds =
            BankStatementEntry::query()
                ->where(
                    'bank_account_id',
                    $bankAccount->id
                )
                ->whereNotNull(
                    'matched_account_ledger_id'
                )
                ->pluck(
                    'matched_account_ledger_id'
                );

        $unmatchedLedgers = $periodLedgers
            ->whereNotIn('id', $matchedLedgerIds)
            ->values();

        $statementOpening =
            $this->statementBalanceBefore(
                $bankAccount,
                $from
            );

        $periodDeposits = round(
            (float) $periodStatementEntries
                ->sum('deposit'),
            2
        );

        $periodWithdrawals = round(
            (float) $periodStatementEntries
                ->sum('withdrawal'),
            2
        );

        $statementClosing = round(
            $statementOpening
            + $periodDeposits
            - $periodWithdrawals,
            2
        );

        $bookOpening = $this->bookBalanceBefore(
            $bankAccount,
            $from
        );

        $periodBookDebit = round(
            (float) $periodLedgers->sum('debit'),
            2
        );

        $periodBookCredit = round(
            (float) $periodLedgers->sum('credit'),
            2
        );

        $bookClosing = round(
            $bookOpening
            + $periodBookDebit
            - $periodBookCredit,
            2
        );

        $unmatchedStatement =
            $periodStatementEntries
                ->where(
                    'reconciliation_status',
                    'Unmatched'
                );

        return [
            'period' => [
                'from' => $from->format('d M Y'),
                'to' => $to->format('d M Y'),
            ],

            'bank_account' => [
                'id' => $bankAccount->id,
                'name' => $bankAccount->display_name,
                'gl_code' =>
                    $bankAccount->accountHead?->code
                    ?: '-',

                'gl_name' =>
                    $bankAccount->accountHead?->name
                    ?: '-',
            ],

            'stats' => [
                'statement_opening' =>
                    $statementOpening,

                'statement_deposits' =>
                    $periodDeposits,

                'statement_withdrawals' =>
                    $periodWithdrawals,

                'statement_closing' =>
                    $statementClosing,

                'book_opening' => $bookOpening,

                'book_debit' => $periodBookDebit,

                'book_credit' => $periodBookCredit,

                'book_closing' => $bookClosing,

                'difference' => round(
                    $statementClosing
                    - $bookClosing,
                    2
                ),

                'matched_count' =>
                    $periodStatementEntries
                        ->where(
                            'reconciliation_status',
                            'Matched'
                        )
                        ->count(),

                'unmatched_statement_count' =>
                    $unmatchedStatement->count(),

                'unmatched_statement_amount' =>
                    round(
                        (float)
                            $unmatchedStatement
                                ->sum('deposit')
                        - (float)
                            $unmatchedStatement
                                ->sum('withdrawal'),
                        2
                    ),

                'unmatched_book_count' =>
                    $unmatchedLedgers->count(),

                'unmatched_book_amount' =>
                    round(
                        (float)
                            $unmatchedLedgers
                                ->sum('debit')
                        - (float)
                            $unmatchedLedgers
                                ->sum('credit'),
                        2
                    ),
            ],

            'statement_rows' =>
                $this->statementRows(
                    $periodStatementEntries
                ),

            'unmatched_book_rows' =>
                $this->ledgerRows(
                    $unmatchedLedgers
                ),
        ];
    }

    private function statementBalanceBefore(
        BankAccount $bankAccount,
        Carbon $from
    ): float {
        $movement =
            BankStatementEntry::query()
                ->where(
                    'bank_account_id',
                    $bankAccount->id
                )
                ->whereDate(
                    'statement_date',
                    '<',
                    $from
                )
                ->selectRaw(
                    'COALESCE(SUM(deposit), 0) - COALESCE(SUM(withdrawal), 0) AS net_movement'
                )
                ->value('net_movement');

        return round(
            (float) $bankAccount->opening_balance
            + (float) $movement,
            2
        );
    }

    private function bookBalanceBefore(
        BankAccount $bankAccount,
        Carbon $from
    ): float {
        $accountHead = $bankAccount->accountHead;

        $openingBalance = round(
            (float) (
                $accountHead?->opening_debit
                ?? 0
            )
            - (float) (
                $accountHead?->opening_credit
                ?? 0
            ),
            2
        );

        $movement = AccountLedger::query()
            ->where(
                'account_head_id',
                $bankAccount->account_head_id
            )
            ->where('status', 'Posted')
            ->whereDate('entry_date', '<', $from)
            ->selectRaw(
                'COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0) AS net_movement'
            )
            ->value('net_movement');

        return round(
            $openingBalance
            + (float) $movement,
            2
        );
    }

    private function statementRows(
        Collection $entries
    ): array {
        return $entries
            ->map(
                fn (
                    BankStatementEntry $entry
                ): array => [
                    'entry_no' =>
                        $entry->entry_no,

                    'date' =>
                        $entry->statement_date
                            ->format('d M Y'),

                    'reference_no' =>
                        $entry->reference_no
                        ?: '-',

                    'description' =>
                        $entry->description
                        ?: '-',

                    'deposit' =>
                        round(
                            (float)
                                $entry->deposit,
                            2
                        ),

                    'withdrawal' =>
                        round(
                            (float)
                                $entry->withdrawal,
                            2
                        ),

                    'running_balance' =>
                        $entry->running_balance
                        !== null
                            ? round(
                                (float)
                                    $entry
                                        ->running_balance,
                                2
                            )
                            : null,

                    'status' =>
                        $entry
                            ->reconciliation_status,

                    'voucher_no' =>
                        $entry->matchedLedger
                            ?->voucher_no
                        ?: '-',

                    'matched_at' =>
                        $entry->matched_at
                            ?->format(
                                'd M Y h:i A'
                            )
                        ?: '-',
                ]
            )
            ->all();
    }

    private function ledgerRows(
        Collection $ledgers
    ): array {
        return $ledgers
            ->map(
                fn (
                    AccountLedger $ledger
                ): array => [
                    'date' =>
                        $ledger->entry_date
                            ->format('d M Y'),

                    'voucher_no' =>
                        $ledger->voucher_no
                        ?: '-',

                    'voucher_type' =>
                        $ledger->voucher_type
                        ?: '-',

                    'reference_no' =>
                        $ledger->reference_no
                        ?: '-',

                    'description' =>
                        $ledger->description
                        ?: '-',

                    'debit' => round(
                        (float) $ledger->debit,
                        2
                    ),

                    'credit' => round(
                        (float) $ledger->credit,
                        2
                    ),
                ]
            )
            ->all();
    }

    private function emptyReport(
        Carbon $from,
        Carbon $to,
    ): array {
        return [
            'period' => [
                'from' => $from->format('d M Y'),
                'to' => $to->format('d M Y'),
            ],

            'bank_account' => null,

            'stats' => [
                'statement_opening' => 0,
                'statement_deposits' => 0,
                'statement_withdrawals' => 0,
                'statement_closing' => 0,
                'book_opening' => 0,
                'book_debit' => 0,
                'book_credit' => 0,
                'book_closing' => 0,
                'difference' => 0,
                'matched_count' => 0,
                'unmatched_statement_count' => 0,
                'unmatched_statement_amount' => 0,
                'unmatched_book_count' => 0,
                'unmatched_book_amount' => 0,
            ],

            'statement_rows' => [],
            'unmatched_book_rows' => [],
        ];
    }
}