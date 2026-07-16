<?php

namespace App\Services;

use App\Models\AccountingPeriodLock;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class AccountingPeriodLockService
{
    public const SCOPE_ALL =
        'All Accounting';

    public const SCOPE_CUSTOMER =
        'Customer Accounting';

    public const SCOPE_SUPPLIER =
        'Supplier Accounting';

    public const SCOPE_EXPENSES =
        'Expenses';

    public const SCOPE_PAYROLL =
        'Payroll';

    public const SCOPE_BANK =
        'Bank Reconciliation';

    public const SCOPE_GENERAL_LEDGER =
        'General Ledger';

    public static function scopeOptions(): array
    {
        return [
            self::SCOPE_ALL =>
                self::SCOPE_ALL,

            self::SCOPE_CUSTOMER =>
                self::SCOPE_CUSTOMER,

            self::SCOPE_SUPPLIER =>
                self::SCOPE_SUPPLIER,

            self::SCOPE_EXPENSES =>
                self::SCOPE_EXPENSES,

            self::SCOPE_PAYROLL =>
                self::SCOPE_PAYROLL,

            self::SCOPE_BANK =>
                self::SCOPE_BANK,

            self::SCOPE_GENERAL_LEDGER =>
                self::SCOPE_GENERAL_LEDGER,
        ];
    }

    public function lock(
        AccountingPeriodLock|int $period,
        int $userId,
    ): AccountingPeriodLock {
        $periodId = $period
            instanceof AccountingPeriodLock
                ? (int) $period->getKey()
                : (int) $period;

        return DB::transaction(
            function () use (
                $periodId,
                $userId
            ): AccountingPeriodLock {
                $period =
                    AccountingPeriodLock::query()
                        ->lockForUpdate()
                        ->findOrFail($periodId);

                if ($period->status === 'Locked') {
                    throw ValidationException::withMessages([
                        'status' =>
                            'Ye accounting period pehle se locked hai.',
                    ]);
                }

                $this->validatePeriodDates(
                    $period
                );

                $overlap =
                    $this
                        ->overlappingLockedPeriodQuery(
                            startDate:
                                $period->period_start,

                            endDate:
                                $period->period_end,

                            scope:
                                $period->lock_scope,

                            ignorePeriodId:
                                (int) $period->id,
                        )
                        ->first();

                if ($overlap) {
                    throw ValidationException::withMessages([
                        'period_start' =>
                            'Selected period existing locked period "'
                            . $overlap->name
                            . '" se overlap karta hai.',
                    ]);
                }

                $period->updateQuietly([
                    'status' => 'Locked',

                    'locked_by' => $userId,
                    'locked_at' => now(),

                    'unlocked_by' => null,
                    'unlocked_at' => null,
                    'unlock_reason' => null,
                ]);

                return $period->refresh();
            }
        );
    }

    public function unlock(
        AccountingPeriodLock|int $period,
        int $userId,
        string $reason,
    ): AccountingPeriodLock {
        $periodId = $period
            instanceof AccountingPeriodLock
                ? (int) $period->getKey()
                : (int) $period;

        return DB::transaction(
            function () use (
                $periodId,
                $userId,
                $reason
            ): AccountingPeriodLock {
                $period =
                    AccountingPeriodLock::query()
                        ->lockForUpdate()
                        ->findOrFail($periodId);

                if ($period->status !== 'Locked') {
                    throw ValidationException::withMessages([
                        'status' =>
                            'Sirf Locked accounting period unlock ho sakta hai.',
                    ]);
                }

                $reason = trim($reason);

                if ($reason === '') {
                    throw ValidationException::withMessages([
                        'reason' =>
                            'Unlock reason required hai.',
                    ]);
                }

                $period->updateQuietly([
                    'status' => 'Unlocked',

                    'unlocked_by' => $userId,
                    'unlocked_at' => now(),

                    'unlock_reason' =>
                        $reason,
                ]);

                return $period->refresh();
            }
        );
    }

    public function assertDateIsOpen(
        string|DateTimeInterface|null $date,
        string $scope = self::SCOPE_ALL,
        string $context =
            'Accounting transaction',
    ): void {
        $transactionDate =
            $this->normalizeDate($date);

        $lockedPeriod =
            $this->lockedPeriodForDate(
                date: $transactionDate,
                scope: $scope,
            );

        if (! $lockedPeriod) {
            return;
        }

        throw ValidationException::withMessages([
            'transaction_date' =>
                $context
                . ' post nahi ho sakta. Date '
                . $transactionDate->format(
                    'd M Y'
                )
                . ' locked accounting period "'
                . $lockedPeriod->name
                . '" mein hai ('
                . $lockedPeriod->period_label
                . ').',
        ]);
    }

    public function assertRangeIsOpen(
        string|DateTimeInterface|null $startDate,
        string|DateTimeInterface|null $endDate,
        string $scope = self::SCOPE_ALL,
        string $context =
            'Accounting transaction',
    ): void {
        $start =
            $this->normalizeDate(
                $startDate
            );

        $end =
            $this->normalizeDate(
                $endDate
            );

        if ($end->lessThan($start)) {
            [$start, $end] = [
                $end,
                $start,
            ];
        }

        $lockedPeriod =
            $this
                ->overlappingLockedPeriodQuery(
                    startDate: $start,
                    endDate: $end,
                    scope: $scope,
                )
                ->orderBy('period_start')
                ->first();

        if (! $lockedPeriod) {
            return;
        }

        throw ValidationException::withMessages([
            'transaction_date' =>
                $context
                . ' locked accounting period "'
                . $lockedPeriod->name
                . '" se overlap karta hai ('
                . $lockedPeriod->period_label
                . ').',
        ]);
    }

    public function isDateLocked(
        string|DateTimeInterface|null $date,
        string $scope = self::SCOPE_ALL,
    ): bool {
        try {
            return $this
                ->lockedPeriodForDate(
                    date:
                        $this->normalizeDate(
                            $date
                        ),

                    scope: $scope,
                ) !== null;
        } catch (Throwable) {
            return false;
        }
    }

    public function lockedPeriodForDate(
        Carbon $date,
        string $scope = self::SCOPE_ALL,
    ): ?AccountingPeriodLock {
        $query =
            AccountingPeriodLock::query()
                ->where(
                    'status',
                    'Locked'
                )
                ->whereDate(
                    'period_start',
                    '<=',
                    $date->format('Y-m-d')
                )
                ->whereDate(
                    'period_end',
                    '>=',
                    $date->format('Y-m-d')
                );

        $this->applyScopeFilter(
            query: $query,
            scope: $scope,
        );

        return $query
            ->orderByDesc('period_start')
            ->first();
    }

    private function overlappingLockedPeriodQuery(
        Carbon $startDate,
        Carbon $endDate,
        string $scope,
        ?int $ignorePeriodId = null,
    ): Builder {
        $query =
            AccountingPeriodLock::query()
                ->where(
                    'status',
                    'Locked'
                )
                ->whereDate(
                    'period_start',
                    '<=',
                    $endDate->format('Y-m-d')
                )
                ->whereDate(
                    'period_end',
                    '>=',
                    $startDate->format('Y-m-d')
                );

        if ($ignorePeriodId) {
            $query->where(
                'id',
                '!=',
                $ignorePeriodId
            );
        }

        $this->applyScopeFilter(
            query: $query,
            scope: $scope,
        );

        return $query;
    }

    private function applyScopeFilter(
        Builder $query,
        string $scope,
    ): void {
        if ($scope === self::SCOPE_ALL) {
            return;
        }

        $query->where(
            function (
                Builder $query
            ) use ($scope): void {
                $query
                    ->where(
                        'lock_scope',
                        self::SCOPE_ALL
                    )
                    ->orWhere(
                        'lock_scope',
                        $scope
                    );
            }
        );
    }

    private function validatePeriodDates(
        AccountingPeriodLock $period
    ): void {
        if (
            ! $period->period_start
            || ! $period->period_end
        ) {
            throw ValidationException::withMessages([
                'period_start' =>
                    'Period Start aur Period End required hain.',
            ]);
        }

        if (
            $period->period_end->lessThan(
                $period->period_start
            )
        ) {
            throw ValidationException::withMessages([
                'period_end' =>
                    'Period End, Period Start se pehle nahi ho sakti.',
            ]);
        }

        if (
            ! array_key_exists(
                $period->lock_scope,
                self::scopeOptions()
            )
        ) {
            throw ValidationException::withMessages([
                'lock_scope' =>
                    'Valid accounting lock scope select karein.',
            ]);
        }
    }

    private function normalizeDate(
        string|DateTimeInterface|null $date
    ): Carbon {
        if ($date instanceof DateTimeInterface) {
            return Carbon::instance($date)
                ->startOfDay();
        }

        if (blank($date)) {
            throw ValidationException::withMessages([
                'transaction_date' =>
                    'Accounting transaction date required hai.',
            ]);
        }

        try {
            return Carbon::parse($date)
                ->startOfDay();
        } catch (Throwable) {
            throw ValidationException::withMessages([
                'transaction_date' =>
                    'Valid accounting transaction date required hai.',
            ]);
        }
    }
}