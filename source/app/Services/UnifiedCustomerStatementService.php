<?php

namespace App\Services;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Throwable;

class UnifiedCustomerStatementService
{
    private const TOLERANCE = 0.01;

    public function generate(
        int|string|null $customerId,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $serviceType = 'all',
    ): array {
        $from = $this->normalizeDate(
            $dateFrom,
            now()->startOfYear()
        )->startOfDay();

        $to = $this->normalizeDate(
            $dateTo,
            today()
        )->endOfDay();

        if ($to->lessThan($from)) {
            [$from, $to] = [
                $to->copy()->startOfDay(),
                $from->copy()->endOfDay(),
            ];
        }

        $missingRequirements =
            $this->missingRequirements();

        if ($missingRequirements !== []) {
            return $this->emptyReport(
                from: $from,
                to: $to,
                missingRequirements:
                    $missingRequirements,
            );
        }

        if (! $customerId) {
            return $this->emptyReport(
                from: $from,
                to: $to,
                customerRequired: true,
            );
        }

        $customer = Customer::query()
            ->find((int) $customerId);

        if (! $customer) {
            return $this->emptyReport(
                from: $from,
                to: $to,
                customerRequired: true,
            );
        }

        $openingBalance =
            $this->openingBalance(
                customerId: (int) $customer->id,
                beforeDate: $from,
                serviceType: $serviceType,
            );

        $transactions = collect()
            ->concat(
                $this->invoiceRows(
                    customerId:
                        (int) $customer->id,

                    from: $from,
                    to: $to,
                    serviceType: $serviceType,
                )
            )
            ->concat(
                $this->receiptRows(
                    customerId:
                        (int) $customer->id,

                    from: $from,
                    to: $to,
                    serviceType: $serviceType,
                )
            )
            ->concat(
                $this->refundRows(
                    customerId:
                        (int) $customer->id,

                    from: $from,
                    to: $to,
                    serviceType: $serviceType,
                )
            )
            ->sortBy(function (
                array $row
            ): string {
                return $row['date_value']
                    . '-'
                    . str_pad(
                        (string)
                            $row['sort_priority'],
                        2,
                        '0',
                        STR_PAD_LEFT
                    )
                    . '-'
                    . str_pad(
                        (string) $row['id'],
                        12,
                        '0',
                        STR_PAD_LEFT
                    );
            })
            ->values();

        $runningBalance = $openingBalance;

        $statementRows = $transactions
            ->map(function (
                array $row
            ) use (
                &$runningBalance
            ): array {
                $runningBalance = round(
                    $runningBalance
                    + $row['debit']
                    - $row['credit'],
                    2
                );

                $row['running_balance'] =
                    $runningBalance;

                $row['balance_label'] =
                    $this->balanceLabel(
                        $runningBalance
                    );

                $row['balance_key'] =
                    $this->balanceKey(
                        $runningBalance
                    );

                return $row;
            })
            ->values();

        $periodDebit = round(
            (float) $statementRows->sum(
                'debit'
            ),
            2
        );

        $periodCredit = round(
            (float) $statementRows->sum(
                'credit'
            ),
            2
        );

        $closingBalance = round(
            $openingBalance
            + $periodDebit
            - $periodCredit,
            2
        );

        return [
            'database_ready' => true,
            'customer_required' => false,
            'missing_requirements' => [],

            'period' => [
                'from' =>
                    $from->format('d M Y'),

                'to' =>
                    $to->format('d M Y'),
            ],

            'customer' => [
                'id' => (int) $customer->id,
                'name' => $customer->name,

                'customer_type' =>
                    $customer->customer_type,

                'status' =>
                    $customer->status,

                'mobile' =>
                    $customer->mobile ?: '-',

                'whatsapp' =>
                    $customer->whatsapp ?: '-',

                'email' =>
                    $customer->email ?: '-',

                'cnic' =>
                    $customer->cnic ?: '-',

                'passport_no' =>
                    $customer->passport_no
                    ?: '-',

                'address' =>
                    $customer->address ?: '-',

                'customer_url' =>
                    $this->resourceUrl(
                        resourceSlug: 'customers',
                        recordId:
                            (int) $customer->id,
                    ),
            ],

            'stats' => [
                'opening_balance' =>
                    $openingBalance,

                'period_debit' =>
                    $periodDebit,

                'period_credit' =>
                    $periodCredit,

                'closing_balance' =>
                    $closingBalance,

                'amount_receivable' =>
                    max(0, $closingBalance),

                'advance_available' =>
                    max(0, -$closingBalance),

                'transaction_count' =>
                    $statementRows->count(),

                'invoice_count' =>
                    $statementRows
                        ->where(
                            'transaction_key',
                            'invoice'
                        )
                        ->count(),

                'receipt_count' =>
                    $statementRows
                        ->whereIn(
                            'transaction_key',
                            [
                                'invoice_receipt',
                                'advance_receipt',
                            ]
                        )
                        ->count(),

                'refund_count' =>
                    $statementRows
                        ->whereIn(
                            'transaction_key',
                            [
                                'invoice_refund',
                                'advance_refund',
                            ]
                        )
                        ->count(),
            ],

            'closing_status' =>
                $this->balanceLabel(
                    $closingBalance
                ),

            'closing_status_key' =>
                $this->balanceKey(
                    $closingBalance
                ),

            'rows' =>
                $statementRows->all(),

            'generated_at' =>
                now()->format(
                    'd M Y h:i A'
                ),
        ];
    }

    private function openingBalance(
        int $customerId,
        Carbon $beforeDate,
        ?string $serviceType,
    ): float {
        $before = $beforeDate
            ->format('Y-m-d');

        $invoiceDebit = DB::table('invoices')
            ->where(
                'customer_id',
                $customerId
            )
            ->whereNotIn('status', [
                'Draft',
                'Cancelled',
            ])
            ->whereDate(
                'invoice_date',
                '<',
                $before
            )
            ->when(
                filled($serviceType)
                && $serviceType !== 'all',
                fn ($query) =>
                    $query->where(
                        'service_type',
                        $serviceType
                    )
            )
            ->sum('total_amount');

        $receiptCredit = DB::table(
            'customer_payment_uploads'
        )
            ->where(
                'customer_id',
                $customerId
            )
            ->where('status', 'Approved')
            ->whereRaw(
                'DATE(COALESCE(payment_date, created_at)) < ?',
                [$before]
            )
            ->when(
                filled($serviceType)
                && $serviceType !== 'all',
                fn ($query) =>
                    $query->where(
                        'service_type',
                        $serviceType
                    )
            )
            ->sum('amount');

        $refundDebit = DB::table('refunds')
            ->where(
                'customer_id',
                $customerId
            )
            ->whereIn('status', [
                'Approved',
                'Paid',
            ])
            ->whereRaw(
                'DATE(COALESCE(refund_date, created_at)) < ?',
                [$before]
            )
            ->when(
                filled($serviceType)
                && $serviceType !== 'all',
                fn ($query) =>
                    $query->where(
                        'service_type',
                        $serviceType
                    )
            )
            ->sum('refund_amount');

        return round(
            (float) $invoiceDebit
            + (float) $refundDebit
            - (float) $receiptCredit,
            2
        );
    }

    private function invoiceRows(
        int $customerId,
        Carbon $from,
        Carbon $to,
        ?string $serviceType,
    ): Collection {
        return DB::table('invoices')
            ->where(
                'customer_id',
                $customerId
            )
            ->whereNotIn('status', [
                'Draft',
                'Cancelled',
            ])
            ->whereBetween('invoice_date', [
                $from->format('Y-m-d'),
                $to->format('Y-m-d'),
            ])
            ->when(
                filled($serviceType)
                && $serviceType !== 'all',
                fn ($query) =>
                    $query->where(
                        'service_type',
                        $serviceType
                    )
            )
            ->orderBy('invoice_date')
            ->orderBy('id')
            ->get()
            ->map(function (
                object $invoice
            ): array {
                $description =
                    $invoice->service_type
                    . ' Invoice';

                if (
                    filled(
                        $invoice
                            ->service_reference
                    )
                ) {
                    $description .= ' | Ref: '
                        . $invoice
                            ->service_reference;
                }

                return [
                    'id' => (int) $invoice->id,
                    'sort_priority' => 10,

                    'date' =>
                        Carbon::parse(
                            $invoice->invoice_date
                        )->format('d M Y'),

                    'date_value' =>
                        Carbon::parse(
                            $invoice->invoice_date
                        )->format('Y-m-d'),

                    'transaction_key' =>
                        'invoice',

                    'transaction_type' =>
                        'Invoice',

                    'source_no' =>
                        $invoice->invoice_no,

                    'service_type' =>
                        $invoice->service_type
                        ?: 'Other',

                    'reference_no' =>
                        $invoice
                            ->service_reference
                        ?: '-',

                    'description' =>
                        $description,

                    'payment_method' => '-',

                    'debit' => round(
                        (float)
                            $invoice->total_amount,
                        2
                    ),

                    'credit' => 0,

                    'status' =>
                        $invoice->status,

                    'source_url' =>
                        $this->resourceUrl(
                            resourceSlug:
                                'invoices',

                            recordId:
                                (int) $invoice->id,
                        ),
                ];
            });
    }

    private function receiptRows(
        int $customerId,
        Carbon $from,
        Carbon $to,
        ?string $serviceType,
    ): Collection {
        return DB::table(
            'customer_payment_uploads'
        )
            ->where(
                'customer_id',
                $customerId
            )
            ->where('status', 'Approved')
            ->whereRaw(
                'DATE(COALESCE(payment_date, created_at)) BETWEEN ? AND ?',
                [
                    $from->format('Y-m-d'),
                    $to->format('Y-m-d'),
                ]
            )
            ->when(
                filled($serviceType)
                && $serviceType !== 'all',
                fn ($query) =>
                    $query->where(
                        'service_type',
                        $serviceType
                    )
            )
            ->orderByRaw(
                'COALESCE(payment_date, DATE(created_at))'
            )
            ->orderBy('id')
            ->get()
            ->map(function (
                object $receipt
            ): array {
                $isAdvance =
                    $receipt->receipt_type
                    === 'Customer Advance';

                $description = $isAdvance
                    ? 'Customer Advance Received'
                    : 'Invoice Payment Received';

                if (
                    filled(
                        $receipt->transaction_id
                    )
                ) {
                    $description .= ' | Txn: '
                        . $receipt
                            ->transaction_id;
                } elseif (
                    filled(
                        $receipt->reference_no
                    )
                ) {
                    $description .= ' | Ref: '
                        . $receipt
                            ->reference_no;
                }

                $date = $receipt->payment_date
                    ?: $receipt->created_at;

                return [
                    'id' => (int) $receipt->id,
                    'sort_priority' => 20,

                    'date' =>
                        Carbon::parse($date)
                            ->format('d M Y'),

                    'date_value' =>
                        Carbon::parse($date)
                            ->format('Y-m-d'),

                    'transaction_key' =>
                        $isAdvance
                            ? 'advance_receipt'
                            : 'invoice_receipt',

                    'transaction_type' =>
                        $isAdvance
                            ? 'Advance Receipt'
                            : 'Invoice Receipt',

                    'source_no' =>
                        $receipt->upload_no,

                    'service_type' =>
                        $receipt->service_type
                        ?: (
                            $isAdvance
                                ? 'Advance'
                                : 'Other'
                        ),

                    'reference_no' =>
                        $receipt->transaction_id
                        ?: $receipt->reference_no
                        ?: '-',

                    'description' =>
                        $description,

                    'payment_method' =>
                        $receipt->payment_method
                        ?: '-',

                    'debit' => 0,

                    'credit' => round(
                        (float) $receipt->amount,
                        2
                    ),

                    'status' =>
                        $receipt->status,

                    'source_url' =>
                        $this->resourceUrl(
                            resourceSlug:
                                'customer-payment-uploads',

                            recordId:
                                (int) $receipt->id,
                        ),
                ];
            });
    }

    private function refundRows(
        int $customerId,
        Carbon $from,
        Carbon $to,
        ?string $serviceType,
    ): Collection {
        return DB::table('refunds')
            ->where(
                'customer_id',
                $customerId
            )
            ->whereIn('status', [
                'Approved',
                'Paid',
            ])
            ->whereRaw(
                'DATE(COALESCE(refund_date, created_at)) BETWEEN ? AND ?',
                [
                    $from->format('Y-m-d'),
                    $to->format('Y-m-d'),
                ]
            )
            ->when(
                filled($serviceType)
                && $serviceType !== 'all',
                fn ($query) =>
                    $query->where(
                        'service_type',
                        $serviceType
                    )
            )
            ->orderByRaw(
                'COALESCE(refund_date, DATE(created_at))'
            )
            ->orderBy('id')
            ->get()
            ->map(function (
                object $refund
            ): array {
                $isAdvance =
                    $refund->refund_type
                    === 'Customer Advance Refund';

                $description = $isAdvance
                    ? 'Customer Advance Refunded'
                    : 'Invoice Payment Refunded';

                if (filled($refund->reason)) {
                    $description .= ' | '
                        . $refund->reason;
                }

                $date = $refund->refund_date
                    ?: $refund->created_at;

                return [
                    'id' => (int) $refund->id,
                    'sort_priority' => 30,

                    'date' =>
                        Carbon::parse($date)
                            ->format('d M Y'),

                    'date_value' =>
                        Carbon::parse($date)
                            ->format('Y-m-d'),

                    'transaction_key' =>
                        $isAdvance
                            ? 'advance_refund'
                            : 'invoice_refund',

                    'transaction_type' =>
                        $isAdvance
                            ? 'Advance Refund'
                            : 'Invoice Refund',

                    'source_no' =>
                        $refund->refund_no,

                    'service_type' =>
                        $refund->service_type
                        ?: (
                            $isAdvance
                                ? 'Advance'
                                : 'Other'
                        ),

                    'reference_no' =>
                        $refund
                            ->service_reference
                        ?: $refund->reference_no
                        ?: '-',

                    'description' =>
                        $description,

                    'payment_method' =>
                        $refund->payment_method
                        ?: '-',

                    'debit' => round(
                        (float)
                            $refund->refund_amount,
                        2
                    ),

                    'credit' => 0,

                    'status' =>
                        $refund->status,

                    'source_url' =>
                        $this->resourceUrl(
                            resourceSlug:
                                'refunds',

                            recordId:
                                (int) $refund->id,
                        ),
                ];
            });
    }

    private function balanceLabel(
        float $balance
    ): string {
        if ($balance > self::TOLERANCE) {
            return 'Customer Outstanding';
        }

        if ($balance < -self::TOLERANCE) {
            return 'Customer Advance';
        }

        return 'Settled';
    }

    private function balanceKey(
        float $balance
    ): string {
        if ($balance > self::TOLERANCE) {
            return 'receivable';
        }

        if ($balance < -self::TOLERANCE) {
            return 'advance';
        }

        return 'settled';
    }

    private function resourceUrl(
        string $resourceSlug,
        int $recordId,
    ): string {
        $routeBase =
            'filament.admin.resources.'
            . $resourceSlug;

        foreach (['view', 'edit'] as $page) {
            $routeName =
                $routeBase . '.' . $page;

            if (! Route::has($routeName)) {
                continue;
            }

            try {
                return route(
                    $routeName,
                    ['record' => $recordId]
                );
            } catch (Throwable) {
                // Continue to fallback.
            }
        }

        return url(
            '/admin/'
            . $resourceSlug
            . '/'
            . $recordId
        );
    }

    private function missingRequirements(): array
    {
        $requiredTables = [
            'customers',
            'invoices',
            'customer_payment_uploads',
            'refunds',
        ];

        $missing = [];

        foreach ($requiredTables as $table) {
            if (! Schema::hasTable($table)) {
                $missing[] =
                    'Missing table: ' . $table;
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
                    'upload_no',
                    'receipt_type',
                    'amount',
                    'payment_date',
                    'status',
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
                    'refund_no',
                    'refund_type',
                    'refund_amount',
                    'refund_date',
                    'status',
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

    private function normalizeDate(
        ?string $value,
        Carbon $fallback,
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
        Carbon $from,
        Carbon $to,
        array $missingRequirements = [],
        bool $customerRequired = false,
    ): array {
        return [
            'database_ready' =>
                $missingRequirements === [],

            'customer_required' =>
                $customerRequired,

            'missing_requirements' =>
                $missingRequirements,

            'period' => [
                'from' =>
                    $from->format('d M Y'),

                'to' =>
                    $to->format('d M Y'),
            ],

            'customer' => null,

            'stats' => [
                'opening_balance' => 0,
                'period_debit' => 0,
                'period_credit' => 0,
                'closing_balance' => 0,
                'amount_receivable' => 0,
                'advance_available' => 0,
                'transaction_count' => 0,
                'invoice_count' => 0,
                'receipt_count' => 0,
                'refund_count' => 0,
            ],

            'closing_status' => 'Settled',
            'closing_status_key' => 'settled',

            'rows' => [],

            'generated_at' =>
                now()->format(
                    'd M Y h:i A'
                ),
        ];
    }
}