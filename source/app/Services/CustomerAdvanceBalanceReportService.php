<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Throwable;

class CustomerAdvanceBalanceReportService
{
    private const TOLERANCE = 0.01;

    public function generate(
        ?string $asOfDate = null,
        ?string $customerType = 'all',
        ?string $customerStatus = 'Active',
        ?string $balanceFilter = 'positive',
        ?string $search = null,
    ): array {
        $asOf = $this->normalizeDate(
            $asOfDate,
            today()
        )->endOfDay();

        $missingRequirements =
            $this->missingRequirements();

        if ($missingRequirements !== []) {
            return $this->emptyReport(
                asOf: $asOf,
                missingRequirements:
                    $missingRequirements,
            );
        }

        $advanceSubQuery =
            $this->advanceSubQuery($asOf);

        $refundSubQuery =
            $this->refundSubQuery($asOf);

        $records = DB::table(
            'customers as customers'
        )
            ->leftJoinSub(
                $advanceSubQuery,
                'advances',
                'advances.customer_id',
                '=',
                'customers.id'
            )
            ->leftJoinSub(
                $refundSubQuery,
                'refunds',
                'refunds.customer_id',
                '=',
                'customers.id'
            )
            ->when(
                filled($customerType)
                && $customerType !== 'all',
                fn (Builder $query) =>
                    $query->where(
                        'customers.customer_type',
                        $customerType
                    )
            )
            ->when(
                filled($customerStatus)
                && $customerStatus !== 'all',
                fn (Builder $query) =>
                    $query->where(
                        'customers.status',
                        $customerStatus
                    )
            )
            ->when(
                filled($search),
                function (
                    Builder $query
                ) use ($search): void {
                    $needle = trim(
                        (string) $search
                    );

                    $query->where(function (
                        Builder $query
                    ) use ($needle): void {
                        $query
                            ->where(
                                'customers.name',
                                'like',
                                '%' . $needle . '%'
                            )
                            ->orWhere(
                                'customers.mobile',
                                'like',
                                '%' . $needle . '%'
                            )
                            ->orWhere(
                                'customers.email',
                                'like',
                                '%' . $needle . '%'
                            )
                            ->orWhere(
                                'customers.cnic',
                                'like',
                                '%' . $needle . '%'
                            )
                            ->orWhere(
                                'customers.passport_no',
                                'like',
                                '%' . $needle . '%'
                            );
                    });
                }
            )
            ->select([
                'customers.id',
                'customers.name',
                'customers.mobile',
                'customers.email',
                'customers.cnic',
                'customers.passport_no',
                'customers.customer_type',
                'customers.status',

                'advances.advance_count',
                'advances.total_advances',
                'advances.last_advance_date',

                'refunds.refund_count',
                'refunds.total_refunds',
                'refunds.last_refund_date',
            ])
            ->orderBy('customers.name')
            ->get();

        $allRows = $records
            ->map(
                fn (object $record): array =>
                    $this->makeRow($record)
            )
            ->filter(
                fn (array $row): bool =>
                    $row['advance_count'] > 0
                    || $row['refund_count'] > 0
            )
            ->values();

        $filteredRows = $allRows
            ->filter(
                fn (array $row): bool =>
                    $this->matchesBalanceFilter(
                        balance:
                            $row['balance_amount'],

                        balanceFilter:
                            $balanceFilter
                    )
            )
            ->sortByDesc(
                'balance_amount'
            )
            ->values();

        return [
            'database_ready' => true,
            'missing_requirements' => [],

            'as_of_date' =>
                $asOf->format('d M Y'),

            'generated_at' =>
                now()->format(
                    'd M Y h:i A'
                ),

            'stats' => [
                'customers' =>
                    $filteredRows->count(),

                'total_advances' => round(
                    (float) $filteredRows->sum(
                        'total_advances'
                    ),
                    2
                ),

                'total_refunds' => round(
                    (float) $filteredRows->sum(
                        'total_refunds'
                    ),
                    2
                ),

                'net_advance_balance' => round(
                    (float) $filteredRows->sum(
                        'balance_amount'
                    ),
                    2
                ),

                'positive_balances' =>
                    $allRows->filter(
                        fn (array $row): bool =>
                            $row['balance_amount']
                            > self::TOLERANCE
                    )->count(),

                'fully_refunded' =>
                    $allRows->filter(
                        fn (array $row): bool =>
                            abs(
                                $row[
                                    'balance_amount'
                                ]
                            ) < self::TOLERANCE
                    )->count(),

                'over_refunded' =>
                    $allRows->filter(
                        fn (array $row): bool =>
                            $row['balance_amount']
                            < -self::TOLERANCE
                    )->count(),
            ],

            'rows' => $filteredRows->all(),
        ];
    }

    private function advanceSubQuery(
        Carbon $asOf
    ): Builder {
        return DB::table(
            'customer_payment_uploads'
        )
            ->select('customer_id')
            ->selectRaw(
                'COUNT(*) as advance_count'
            )
            ->selectRaw(
                'SUM(amount) as total_advances'
            )
            ->selectRaw(
                'MAX(COALESCE(payment_date, DATE(created_at))) as last_advance_date'
            )
            ->where(
                'status',
                'Approved'
            )
            ->where(
                'receipt_type',
                'Customer Advance'
            )
            ->where(function (
                Builder $query
            ) use ($asOf): void {
                $query
                    ->whereDate(
                        'payment_date',
                        '<=',
                        $asOf->format('Y-m-d')
                    )
                    ->orWhere(function (
                        Builder $query
                    ) use ($asOf): void {
                        $query
                            ->whereNull(
                                'payment_date'
                            )
                            ->whereDate(
                                'created_at',
                                '<=',
                                $asOf->format(
                                    'Y-m-d'
                                )
                            );
                    });
            })
            ->groupBy('customer_id');
    }

    private function refundSubQuery(
        Carbon $asOf
    ): Builder {
        return DB::table('refunds')
            ->select('customer_id')
            ->selectRaw(
                'COUNT(*) as refund_count'
            )
            ->selectRaw(
                'SUM(refund_amount) as total_refunds'
            )
            ->selectRaw(
                'MAX(COALESCE(refund_date, DATE(created_at))) as last_refund_date'
            )
            ->where(
                'status',
                'Approved'
            )
            ->where(
                'refund_type',
                'Customer Advance Refund'
            )
            ->where(function (
                Builder $query
            ) use ($asOf): void {
                $query
                    ->whereDate(
                        'refund_date',
                        '<=',
                        $asOf->format('Y-m-d')
                    )
                    ->orWhere(function (
                        Builder $query
                    ) use ($asOf): void {
                        $query
                            ->whereNull(
                                'refund_date'
                            )
                            ->whereDate(
                                'created_at',
                                '<=',
                                $asOf->format(
                                    'Y-m-d'
                                )
                            );
                    });
            })
            ->groupBy('customer_id');
    }

    private function makeRow(
        object $record
    ): array {
        $totalAdvances = round(
            (float) (
                $record->total_advances
                ?? 0
            ),
            2
        );

        $totalRefunds = round(
            (float) (
                $record->total_refunds
                ?? 0
            ),
            2
        );

        $balance = round(
            $totalAdvances - $totalRefunds,
            2
        );

        return [
            'customer_id' =>
                (int) $record->id,

            'customer_name' =>
                $record->name,

            'customer_type' =>
                $record->customer_type,

            'customer_status' =>
                $record->status,

            'mobile' =>
                $record->mobile ?: '-',

            'email' =>
                $record->email ?: '-',

            'cnic' =>
                $record->cnic ?: '-',

            'passport_no' =>
                $record->passport_no ?: '-',

            'advance_count' =>
                (int) (
                    $record->advance_count
                    ?? 0
                ),

            'total_advances' =>
                $totalAdvances,

            'refund_count' =>
                (int) (
                    $record->refund_count
                    ?? 0
                ),

            'total_refunds' =>
                $totalRefunds,

            'balance_amount' =>
                $balance,

            'balance_status' =>
                $this->balanceStatus($balance),

            'balance_status_key' =>
                $this->balanceStatusKey(
                    $balance
                ),

            'last_advance_date' =>
                $this->formatDate(
                    $record
                        ->last_advance_date
                    ?? null
                ),

            'last_refund_date' =>
                $this->formatDate(
                    $record
                        ->last_refund_date
                    ?? null
                ),

            'customer_url' =>
                $this->customerUrl(
                    (int) $record->id
                ),
        ];
    }

    private function matchesBalanceFilter(
        float $balance,
        ?string $balanceFilter
    ): bool {
        return match ($balanceFilter) {
            'positive' =>
                $balance
                > self::TOLERANCE,

            'zero' =>
                abs($balance)
                < self::TOLERANCE,

            'negative' =>
                $balance
                < -self::TOLERANCE,

            default => true,
        };
    }

    private function balanceStatus(
        float $balance
    ): string {
        if ($balance > self::TOLERANCE) {
            return 'Advance Available';
        }

        if ($balance < -self::TOLERANCE) {
            return 'Over Refunded';
        }

        return 'Fully Refunded';
    }

    private function balanceStatusKey(
        float $balance
    ): string {
        if ($balance > self::TOLERANCE) {
            return 'positive';
        }

        if ($balance < -self::TOLERANCE) {
            return 'negative';
        }

        return 'zero';
    }

    private function customerUrl(
        int $customerId
    ): string {
        $routeBase =
            'filament.admin.resources.customers';

        foreach (['view', 'edit'] as $page) {
            $routeName =
                $routeBase . '.' . $page;

            if (! Route::has($routeName)) {
                continue;
            }

            try {
                return route(
                    $routeName,
                    [
                        'record' =>
                            $customerId,
                    ]
                );
            } catch (Throwable) {
                // Fallback URL below.
            }
        }

        return url(
            '/admin/customers/'
            . $customerId
        );
    }

    private function missingRequirements(): array
    {
        $missing = [];

        foreach (
            [
                'customers',
                'customer_payment_uploads',
                'refunds',
            ] as $table
        ) {
            if (! Schema::hasTable($table)) {
                $missing[] =
                    'Missing table: '
                    . $table;
            }
        }

        if (
            Schema::hasTable(
                'customer_payment_uploads'
            )
        ) {
            foreach (
                [
                    'customer_id',
                    'receipt_type',
                    'amount',
                    'status',
                    'payment_date',
                ] as $column
            ) {
                if (
                    ! Schema::hasColumn(
                        'customer_payment_uploads',
                        $column
                    )
                ) {
                    $missing[] =
                        'Missing column: customer_payment_uploads.'
                        . $column;
                }
            }
        }

        if (Schema::hasTable('refunds')) {
            foreach (
                [
                    'customer_id',
                    'refund_type',
                    'refund_amount',
                    'status',
                    'refund_date',
                ] as $column
            ) {
                if (
                    ! Schema::hasColumn(
                        'refunds',
                        $column
                    )
                ) {
                    $missing[] =
                        'Missing column: refunds.'
                        . $column;
                }
            }
        }

        return $missing;
    }

    private function formatDate(
        mixed $value
    ): string {
        if (blank($value)) {
            return '-';
        }

        try {
            return Carbon::parse($value)
                ->format('d M Y');
        } catch (Throwable) {
            return '-';
        }
    }

    private function normalizeDate(
        ?string $value,
        Carbon $fallback
    ): Carbon {
        if (blank($value)) {
            return $fallback->copy();
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            return $fallback->copy();
        }
    }

    private function emptyReport(
        Carbon $asOf,
        array $missingRequirements
    ): array {
        return [
            'database_ready' => false,

            'missing_requirements' =>
                $missingRequirements,

            'as_of_date' =>
                $asOf->format('d M Y'),

            'generated_at' =>
                now()->format(
                    'd M Y h:i A'
                ),

            'stats' => [
                'customers' => 0,
                'total_advances' => 0,
                'total_refunds' => 0,
                'net_advance_balance' => 0,
                'positive_balances' => 0,
                'fully_refunded' => 0,
                'over_refunded' => 0,
            ],

            'rows' => [],
        ];
    }
}