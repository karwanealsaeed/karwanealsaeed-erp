<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Throwable;

class UnifiedSupplierStatementService
{
    private const TOLERANCE = 0.01;

    public function generate(
        int|string|null $supplierId,
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

        $schema = $this->schemaMap();

        if ($schema['errors'] !== []) {
            return $this->emptyReport(
                from: $from,
                to: $to,
                missingRequirements: $schema['errors'],
            );
        }

        if (! $supplierId) {
            return $this->emptyReport(
                from: $from,
                to: $to,
                supplierRequired: true,
            );
        }

        $supplier = DB::table('suppliers')
            ->where('id', (int) $supplierId)
            ->first();

        if (! $supplier) {
            return $this->emptyReport(
                from: $from,
                to: $to,
                supplierRequired: true,
            );
        }

        $openingBalance = $this->openingBalance(
            supplierId: (int) $supplier->id,
            beforeDate: $from,
            serviceType: $serviceType,
            schema: $schema,
        );

        $transactions = collect()
            ->concat(
                $this->billRows(
                    supplierId: (int) $supplier->id,
                    from: $from,
                    to: $to,
                    serviceType: $serviceType,
                    schema: $schema,
                )
            )
            ->concat(
                $this->paymentRows(
                    supplierId: (int) $supplier->id,
                    from: $from,
                    to: $to,
                    serviceType: $serviceType,
                    schema: $schema,
                )
            )
            ->sortBy(
                fn (array $row): string =>
                    $row['date_value']
                    . '-'
                    . str_pad(
                        (string) $row['sort_priority'],
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
                    )
            )
            ->values();

        $runningBalance = $openingBalance;

        $statementRows = $transactions
            ->map(function (array $row) use (
                &$runningBalance
            ): array {
                $runningBalance = round(
                    $runningBalance
                    + $row['credit']
                    - $row['debit'],
                    2
                );

                $row['running_balance'] = $runningBalance;
                $row['balance_label'] = $this->balanceLabel(
                    $runningBalance
                );
                $row['balance_key'] = $this->balanceKey(
                    $runningBalance
                );

                return $row;
            })
            ->values();

        $periodDebit = round(
            (float) $statementRows->sum('debit'),
            2
        );

        $periodCredit = round(
            (float) $statementRows->sum('credit'),
            2
        );

        $closingBalance = round(
            $openingBalance
            + $periodCredit
            - $periodDebit,
            2
        );

        $supplierNameColumn = $schema['supplier_name'];
        $supplierStatusColumn = $schema['supplier_status'];
        $supplierTypeColumn = $schema['supplier_type'];
        $supplierPhoneColumn = $schema['supplier_phone'];
        $supplierEmailColumn = $schema['supplier_email'];
        $supplierAddressColumn = $schema['supplier_address'];

        return [
            'database_ready' => true,
            'supplier_required' => false,
            'missing_requirements' => [],

            'period' => [
                'from' => $from->format('d M Y'),
                'to' => $to->format('d M Y'),
            ],

            'supplier' => [
                'id' => (int) $supplier->id,

                'name' => $this->value(
                    $supplier,
                    $supplierNameColumn,
                    'Supplier'
                ),

                'supplier_type' => $this->value(
                    $supplier,
                    $supplierTypeColumn,
                    '-'
                ),

                'status' => $this->value(
                    $supplier,
                    $supplierStatusColumn,
                    '-'
                ),

                'phone' => $this->value(
                    $supplier,
                    $supplierPhoneColumn,
                    '-'
                ),

                'email' => $this->value(
                    $supplier,
                    $supplierEmailColumn,
                    '-'
                ),

                'address' => $this->value(
                    $supplier,
                    $supplierAddressColumn,
                    '-'
                ),

                'supplier_url' => $this->resourceUrl(
                    resourceSlug: 'suppliers',
                    recordId: (int) $supplier->id,
                ),
            ],

            'stats' => [
                'opening_balance' => $openingBalance,
                'period_debit' => $periodDebit,
                'period_credit' => $periodCredit,
                'closing_balance' => $closingBalance,

                'amount_payable' => max(
                    0,
                    $closingBalance
                ),

                'supplier_advance' => max(
                    0,
                    -$closingBalance
                ),

                'transaction_count' => $statementRows->count(),

                'bill_count' => $statementRows
                    ->where('transaction_key', 'bill')
                    ->count(),

                'payment_count' => $statementRows
                    ->where('transaction_key', 'payment')
                    ->count(),
            ],

            'closing_status' => $this->balanceLabel(
                $closingBalance
            ),

            'closing_status_key' => $this->balanceKey(
                $closingBalance
            ),

            'rows' => $statementRows->all(),

            'generated_at' => now()->format(
                'd M Y h:i A'
            ),
        ];
    }

    private function openingBalance(
        int $supplierId,
        Carbon $beforeDate,
        ?string $serviceType,
        array $schema,
    ): float {
        $before = $beforeDate->format('Y-m-d');

        $billQuery = $this->billQuery(
            supplierId: $supplierId,
            serviceType: $serviceType,
            schema: $schema,
        );

        $billQuery->whereDate(
            $schema['bill_date'],
            '<',
            $before
        );

        $paymentQuery = $this->paymentQuery(
            supplierId: $supplierId,
            serviceType: $serviceType,
            schema: $schema,
        );

        $paymentQuery->whereDate(
            $schema['payment_date'],
            '<',
            $before
        );

        $bills = round(
            (float) $billQuery->sum(
                $schema['bill_amount']
            ),
            2
        );

        $payments = round(
            (float) $paymentQuery->sum(
                $schema['payment_amount']
            ),
            2
        );

        return round(
            $bills - $payments,
            2
        );
    }

    private function billRows(
        int $supplierId,
        Carbon $from,
        Carbon $to,
        ?string $serviceType,
        array $schema,
    ): Collection {
        return $this->billQuery(
            supplierId: $supplierId,
            serviceType: $serviceType,
            schema: $schema,
        )
            ->whereDate(
                $schema['bill_date'],
                '>=',
                $from->format('Y-m-d')
            )
            ->whereDate(
                $schema['bill_date'],
                '<=',
                $to->format('Y-m-d')
            )
            ->orderBy($schema['bill_date'])
            ->orderBy('id')
            ->get()
            ->map(function (object $bill) use (
                $schema
            ): array {
                $dateValue = $this->dateValue(
                    $this->value(
                        $bill,
                        $schema['bill_date'],
                        now()
                    )
                );

                $billNo = $this->value(
                    $bill,
                    $schema['bill_no'],
                    'BILL-' . $bill->id
                );

                $serviceType = $this->value(
                    $bill,
                    $schema['bill_service_type'],
                    'Other'
                );

                $reference = $this->value(
                    $bill,
                    $schema['bill_reference'],
                    '-'
                );

                $description = $this->value(
                    $bill,
                    $schema['bill_description'],
                    'Supplier Bill'
                );

                $status = $this->value(
                    $bill,
                    $schema['bill_status'],
                    'Posted'
                );

                $dueDate = $this->formatOptionalDate(
                    $this->value(
                        $bill,
                        $schema['bill_due_date'],
                        null
                    )
                );

                if ($dueDate !== '-') {
                    $description .= ' | Due: '
                        . $dueDate;
                }

                return [
                    'id' => (int) $bill->id,
                    'sort_priority' => 10,

                    'date' => Carbon::parse(
                        $dateValue
                    )->format('d M Y'),

                    'date_value' => $dateValue,

                    'transaction_key' => 'bill',
                    'transaction_type' => 'Supplier Bill',

                    'source_no' => (string) $billNo,
                    'service_type' => (string) $serviceType,
                    'reference_no' => (string) $reference,
                    'description' => (string) $description,
                    'payment_method' => '-',

                    'debit' => 0,

                    'credit' => round(
                        (float) $this->value(
                            $bill,
                            $schema['bill_amount'],
                            0
                        ),
                        2
                    ),

                    'status' => (string) $status,

                    'source_url' => $this->resourceUrl(
                        resourceSlug: 'supplier-bills',
                        recordId: (int) $bill->id,
                    ),
                ];
            });
    }

    private function paymentRows(
        int $supplierId,
        Carbon $from,
        Carbon $to,
        ?string $serviceType,
        array $schema,
    ): Collection {
        return $this->paymentQuery(
            supplierId: $supplierId,
            serviceType: $serviceType,
            schema: $schema,
        )
            ->whereDate(
                $schema['payment_date'],
                '>=',
                $from->format('Y-m-d')
            )
            ->whereDate(
                $schema['payment_date'],
                '<=',
                $to->format('Y-m-d')
            )
            ->orderBy($schema['payment_date'])
            ->orderBy('id')
            ->get()
            ->map(function (object $payment) use (
                $schema
            ): array {
                $dateValue = $this->dateValue(
                    $this->value(
                        $payment,
                        $schema['payment_date'],
                        now()
                    )
                );

                $paymentNo = $this->value(
                    $payment,
                    $schema['payment_no'],
                    'PAY-' . $payment->id
                );

                $serviceType = $this->value(
                    $payment,
                    $schema['payment_service_type'],
                    'Other'
                );

                $reference = $this->value(
                    $payment,
                    $schema['payment_reference'],
                    '-'
                );

                $description = $this->value(
                    $payment,
                    $schema['payment_description'],
                    'Supplier Payment'
                );

                $paymentMethod = $this->value(
                    $payment,
                    $schema['payment_method'],
                    '-'
                );

                $status = $this->value(
                    $payment,
                    $schema['payment_status'],
                    'Approved'
                );

                return [
                    'id' => (int) $payment->id,
                    'sort_priority' => 20,

                    'date' => Carbon::parse(
                        $dateValue
                    )->format('d M Y'),

                    'date_value' => $dateValue,

                    'transaction_key' => 'payment',
                    'transaction_type' => 'Supplier Payment',

                    'source_no' => (string) $paymentNo,
                    'service_type' => (string) $serviceType,
                    'reference_no' => (string) $reference,
                    'description' => (string) $description,
                    'payment_method' => (string) $paymentMethod,

                    'debit' => round(
                        (float) $this->value(
                            $payment,
                            $schema['payment_amount'],
                            0
                        ),
                        2
                    ),

                    'credit' => 0,

                    'status' => (string) $status,

                    'source_url' => $this->resourceUrl(
                        resourceSlug: 'supplier-payments',
                        recordId: (int) $payment->id,
                    ),
                ];
            });
    }

    private function billQuery(
        int $supplierId,
        ?string $serviceType,
        array $schema,
    ): Builder {
        $query = DB::table('supplier_bills')
            ->where('supplier_id', $supplierId);

        if ($schema['bill_status']) {
            $query->whereNotIn(
                $schema['bill_status'],
                [
                    'Draft',
                    'Pending',
                    'Rejected',
                    'Cancelled',
                    'Canceled',
                    'Void',
                    'Voided',
                ]
            );
        }

        if (
            $schema['bill_service_type']
            && filled($serviceType)
            && $serviceType !== 'all'
        ) {
            $query->where(
                $schema['bill_service_type'],
                $serviceType
            );
        }

        return $query;
    }

    private function paymentQuery(
        int $supplierId,
        ?string $serviceType,
        array $schema,
    ): Builder {
        $query = DB::table('supplier_payments')
            ->where('supplier_id', $supplierId);

        if ($schema['payment_status']) {
            $query->whereIn(
                $schema['payment_status'],
                [
                    'Approved',
                    'Paid',
                    'Posted',
                    'Completed',
                ]
            );
        }

        if (
            $schema['payment_service_type']
            && filled($serviceType)
            && $serviceType !== 'all'
        ) {
            $query->where(
                $schema['payment_service_type'],
                $serviceType
            );
        }

        return $query;
    }

    private function schemaMap(): array
    {
        $errors = [];

        foreach (
            [
                'suppliers',
                'supplier_bills',
                'supplier_payments',
            ] as $table
        ) {
            if (! Schema::hasTable($table)) {
                $errors[] = 'Missing table: ' . $table;
            }
        }

        if ($errors !== []) {
            return ['errors' => $errors];
        }

        $supplierName = $this->firstColumn(
            'suppliers',
            [
                'name',
                'supplier_name',
                'company_name',
            ]
        );

        $billAmount = $this->firstColumn(
            'supplier_bills',
            [
                'total_amount',
                'bill_amount',
                'amount',
                'grand_total',
            ]
        );

        $billDate = $this->firstColumn(
            'supplier_bills',
            [
                'bill_date',
                'date',
                'created_at',
            ]
        );

        $paymentAmount = $this->firstColumn(
            'supplier_payments',
            [
                'amount',
                'payment_amount',
                'total_amount',
            ]
        );

        $paymentDate = $this->firstColumn(
            'supplier_payments',
            [
                'payment_date',
                'date',
                'created_at',
            ]
        );

        foreach (
            [
                'suppliers.name' => $supplierName,
                'supplier_bills.supplier_id' =>
                    Schema::hasColumn(
                        'supplier_bills',
                        'supplier_id'
                    )
                        ? 'supplier_id'
                        : null,

                'supplier_bills amount' => $billAmount,
                'supplier_bills date' => $billDate,

                'supplier_payments.supplier_id' =>
                    Schema::hasColumn(
                        'supplier_payments',
                        'supplier_id'
                    )
                        ? 'supplier_id'
                        : null,

                'supplier_payments amount' => $paymentAmount,
                'supplier_payments date' => $paymentDate,
            ] as $requirement => $column
        ) {
            if (! $column) {
                $errors[] = 'Missing required column: '
                    . $requirement;
            }
        }

        return [
            'errors' => $errors,

            'supplier_name' => $supplierName,

            'supplier_status' => $this->firstColumn(
                'suppliers',
                ['status']
            ),

            'supplier_type' => $this->firstColumn(
                'suppliers',
                [
                    'supplier_type',
                    'type',
                    'category',
                ]
            ),

            'supplier_phone' => $this->firstColumn(
                'suppliers',
                [
                    'mobile',
                    'phone',
                    'contact_number',
                ]
            ),

            'supplier_email' => $this->firstColumn(
                'suppliers',
                ['email']
            ),

            'supplier_address' => $this->firstColumn(
                'suppliers',
                ['address']
            ),

            'bill_amount' => $billAmount,
            'bill_date' => $billDate,

            'bill_no' => $this->firstColumn(
                'supplier_bills',
                [
                    'bill_no',
                    'bill_number',
                    'reference_no',
                ]
            ),

            'bill_status' => $this->firstColumn(
                'supplier_bills',
                [
                    'status',
                    'approval_status',
                ]
            ),

            'bill_service_type' => $this->firstColumn(
                'supplier_bills',
                [
                    'service_type',
                    'category',
                ]
            ),

            'bill_reference' => $this->firstColumn(
                'supplier_bills',
                [
                    'reference_no',
                    'supplier_invoice_no',
                    'invoice_no',
                ]
            ),

            'bill_description' => $this->firstColumn(
                'supplier_bills',
                [
                    'description',
                    'notes',
                    'remarks',
                ]
            ),

            'bill_due_date' => $this->firstColumn(
                'supplier_bills',
                ['due_date']
            ),

            'payment_amount' => $paymentAmount,
            'payment_date' => $paymentDate,

            'payment_no' => $this->firstColumn(
                'supplier_payments',
                [
                    'payment_no',
                    'payment_number',
                    'reference_no',
                ]
            ),

            'payment_status' => $this->firstColumn(
                'supplier_payments',
                ['status']
            ),

            'payment_service_type' => $this->firstColumn(
                'supplier_payments',
                [
                    'service_type',
                    'category',
                ]
            ),

            'payment_reference' => $this->firstColumn(
                'supplier_payments',
                [
                    'transaction_id',
                    'reference_no',
                    'cheque_no',
                ]
            ),

            'payment_description' => $this->firstColumn(
                'supplier_payments',
                [
                    'description',
                    'notes',
                    'remarks',
                ]
            ),

            'payment_method' => $this->firstColumn(
                'supplier_payments',
                [
                    'payment_method',
                    'method',
                ]
            ),
        ];
    }

    private function firstColumn(
        string $table,
        array $candidates,
    ): ?string {
        foreach ($candidates as $column) {
            if (Schema::hasColumn($table, $column)) {
                return $column;
            }
        }

        return null;
    }

    private function value(
        object $record,
        ?string $column,
        mixed $fallback = null,
    ): mixed {
        if (! $column) {
            return $fallback;
        }

        $value = $record->{$column} ?? null;

        return filled($value)
            ? $value
            : $fallback;
    }

    private function dateValue(mixed $value): string
    {
        try {
            return Carbon::parse($value)
                ->format('Y-m-d');
        } catch (Throwable) {
            return today()->format('Y-m-d');
        }
    }

    private function formatOptionalDate(
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

    private function balanceLabel(
        float $balance
    ): string {
        if ($balance > self::TOLERANCE) {
            return 'Supplier Payable';
        }

        if ($balance < -self::TOLERANCE) {
            return 'Supplier Advance';
        }

        return 'Settled';
    }

    private function balanceKey(
        float $balance
    ): string {
        if ($balance > self::TOLERANCE) {
            return 'payable';
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
        $routeBase = 'filament.admin.resources.'
            . $resourceSlug;

        foreach (['view', 'edit'] as $page) {
            $routeName = $routeBase . '.' . $page;

            if (! Route::has($routeName)) {
                continue;
            }

            try {
                return route(
                    $routeName,
                    ['record' => $recordId]
                );
            } catch (Throwable) {
                // Fallback below.
            }
        }

        return url(
            '/admin/'
            . $resourceSlug
            . '/'
            . $recordId
        );
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
        bool $supplierRequired = false,
    ): array {
        return [
            'database_ready' =>
                $missingRequirements === [],

            'supplier_required' => $supplierRequired,

            'missing_requirements' =>
                $missingRequirements,

            'period' => [
                'from' => $from->format('d M Y'),
                'to' => $to->format('d M Y'),
            ],

            'supplier' => null,

            'stats' => [
                'opening_balance' => 0,
                'period_debit' => 0,
                'period_credit' => 0,
                'closing_balance' => 0,
                'amount_payable' => 0,
                'supplier_advance' => 0,
                'transaction_count' => 0,
                'bill_count' => 0,
                'payment_count' => 0,
            ],

            'closing_status' => 'Settled',
            'closing_status_key' => 'settled',
            'rows' => [],

            'generated_at' => now()->format(
                'd M Y h:i A'
            ),
        ];
    }
}