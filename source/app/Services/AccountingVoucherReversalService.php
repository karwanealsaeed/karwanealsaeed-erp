<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AccountVoucher;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class AccountingVoucherReversalService
{
    public function __construct(
        private readonly AccountVoucherPostingValidator $validator,
        private readonly AccountingPeriodLockService $periodLocks,
    ) {
    }

    public function reverse(
        AccountVoucher|int $original,
        string $reason,
        ?string $reversalDate = null,
        ?int $createdBy = null,
    ): AccountVoucher {
        $date =
            $reversalDate
            ?: now()->toDateString();

        $reason = trim($reason);

        if ($reason === '') {
            throw ValidationException::withMessages([
                'reversal_reason' =>
                    'Reversal reason is required.',
            ]);
        }

        $this->periodLocks->assertDateIsOpen(
            date: $date,
            scope:
                AccountingPeriodLockService::SCOPE_GENERAL_LEDGER,
            context:
                'Accounting Voucher Reversal',
        );

        return DB::transaction(
            function () use (
                $original,
                $reason,
                $date,
                $createdBy,
            ): AccountVoucher {
                $originalId =
                    $original instanceof AccountVoucher
                        ? (int) $original->getKey()
                        : (int) $original;

                $lockedOriginal =
                    AccountVoucher::query()
                        ->with([
                            'lines',
                            'ledgers',
                            'reversal.lines',
                            'reversal.ledgers',
                        ])
                        ->lockForUpdate()
                        ->findOrFail($originalId);

                if (
                    $lockedOriginal
                        ->reversal_of_voucher_id
                ) {
                    throw ValidationException::withMessages([
                        'voucher' =>
                            'A reversal voucher cannot be reversed through this service.',
                    ]);
                }

                if ($lockedOriginal->reversal) {
                    $this->assertValidExistingReversal(
                        $lockedOriginal,
                        $lockedOriginal->reversal,
                    );

                    return $lockedOriginal
                        ->reversal
                        ->fresh([
                            'lines',
                            'ledgers',
                            'reversalOf',
                        ]);
                }

                if (
                    $lockedOriginal->status !== 'Posted'
                ) {
                    throw ValidationException::withMessages([
                        'voucher' =>
                            'Only a Posted voucher can be reversed.',
                    ]);
                }

                if (
                    ! $this->validator->isBalanced(
                        $lockedOriginal
                            ->lines
                            ->all(),
                    )
                ) {
                    throw ValidationException::withMessages([
                        'voucher' =>
                            'Original voucher lines must be balanced before reversal.',
                    ]);
                }

                if (
                    $lockedOriginal->lines->count()
                    !== $lockedOriginal->ledgers->count()
                    || $lockedOriginal
                        ->ledgers
                        ->contains(
                            fn ($ledger): bool =>
                                $ledger->status
                                    !== 'Posted',
                        )
                ) {
                    throw ValidationException::withMessages([
                        'voucher' =>
                            'Original voucher must have complete Posted GL entries before reversal.',
                    ]);
                }

                $reversal =
                    AccountVoucher::query()
                        ->create([
                            'voucher_date' => $date,
                            'voucher_type' => 'Journal',
                            'reference_no' =>
                                'REV-'
                                .$lockedOriginal
                                    ->voucher_no,
                            'party_name' =>
                                'Accounting Reversal',
                            'description' =>
                                'Reversal of '
                                .$lockedOriginal
                                    ->voucher_no,
                            'total_debit' => 0,
                            'total_credit' => 0,
                            'status' => 'Draft',
                            'created_by' =>
                                $createdBy,
                            'source_model' =>
                                AccountVoucher::class,
                            'source_id' =>
                                $lockedOriginal->id,
                            'source_type' =>
                                'AccountingReversal',
                            'source_no' =>
                                $lockedOriginal
                                    ->voucher_no,
                            'reversal_of_voucher_id' =>
                                $lockedOriginal->id,
                            'reversal_reason' =>
                                $reason,
                        ]);

                foreach (
                    $lockedOriginal->lines
                    as $index => $line
                ) {
                    $reversal
                        ->lines()
                        ->create([
                            'account_head_id' =>
                                $line
                                    ->account_head_id,
                            'description' =>
                                'Reverse '
                                .$lockedOriginal
                                    ->voucher_no
                                .' - '
                                .(
                                    $line->description
                                    ?: $lockedOriginal
                                        ->description
                                    ?: 'Accounting entry'
                                ),
                            'debit' =>
                                round(
                                    (float)
                                        $line->credit,
                                    2,
                                ),
                            'credit' =>
                                round(
                                    (float)
                                        $line->debit,
                                    2,
                                ),
                            'sort_order' =>
                                $index + 1,
                        ]);
                }

                $reversal->refreshTotals();
                $reversal->load('lines');

                $this->validator->assertCanPost(
                    $reversal
                        ->lines
                        ->all(),
                );

                $reversal->status = 'Posted';
                $reversal->save();
                $reversal->refreshTotals();
                $reversal->syncLedgerEntries();

                $reversal->refresh()
                    ->load([
                        'lines',
                        'ledgers',
                        'reversalOf',
                    ]);

                $this->assertValidExistingReversal(
                    $lockedOriginal,
                    $reversal,
                );

                return $reversal;
            },
        );
    }

    private function assertValidExistingReversal(
        AccountVoucher $original,
        AccountVoucher $reversal,
    ): void {
        $original->loadMissing([
            'lines',
            'ledgers',
        ]);

        $reversal->loadMissing([
            'lines',
            'ledgers',
        ]);

        if (
            $reversal->status !== 'Posted'
            || (int)
                $reversal
                    ->reversal_of_voucher_id
                !== (int) $original->id
            || $reversal->lines->count()
                !== $original->lines->count()
            || $reversal->ledgers->count()
                !== $reversal->lines->count()
            || ! $this->validator->isBalanced(
                $reversal->lines->all(),
            )
        ) {
            throw ValidationException::withMessages([
                'voucher' =>
                    'Existing linked reversal is incomplete or invalid.',
            ]);
        }

        $expected =
            $original
                ->lines
                ->map(
                    static fn ($line): string =>
                        sprintf(
                            '%020d|%020.2f|%020.2f',
                            (int)
                                $line
                                    ->account_head_id,
                            round(
                                (float) $line->credit,
                                2,
                            ),
                            round(
                                (float) $line->debit,
                                2,
                            ),
                        ),
                )
                ->sort()
                ->values()
                ->all();

        $actual =
            $reversal
                ->lines
                ->map(
                    static fn ($line): string =>
                        sprintf(
                            '%020d|%020.2f|%020.2f',
                            (int)
                                $line
                                    ->account_head_id,
                            round(
                                (float) $line->debit,
                                2,
                            ),
                            round(
                                (float) $line->credit,
                                2,
                            ),
                        ),
                )
                ->sort()
                ->values()
                ->all();

        if ($expected !== $actual) {
            throw ValidationException::withMessages([
                'voucher' =>
                    'Existing linked reversal does not mirror the original voucher.',
            ]);
        }
    }
}