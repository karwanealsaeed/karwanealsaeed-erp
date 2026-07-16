<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class AccountingApprovalCenterService
{
    public function generate(
        ?string $queueType = 'all',
        ?string $search = null,
    ): array {
        $allRows = collect()
            ->concat($this->supplierBillRows())
            ->concat($this->supplierPaymentRows())
            ->concat($this->customerPaymentUploadRows())
            ->concat($this->staffLedgerRows())
            ->concat($this->refundRows())
            ->sortByDesc(
                fn (array $row): string =>
                    $row['date_value']
                    . '-'
                    . str_pad(
                        (string) $row['id'],
                        12,
                        '0',
                        STR_PAD_LEFT
                    )
            )
            ->values();

        $filteredRows = $allRows;

        if (
            filled($queueType)
            && $queueType !== 'all'
        ) {
            $filteredRows = $filteredRows
                ->where('queue_key', $queueType)
                ->values();
        }

        if (filled($search)) {
            $needle = Str::lower(
                trim((string) $search)
            );

            $filteredRows = $filteredRows
                ->filter(
                    fn (array $row): bool =>
                        Str::contains(
                            Str::lower(
                                $row['search_text']
                            ),
                            $needle
                        )
                )
                ->values();
        }

        return [
            'generated_at' =>
                now()->format('d M Y h:i A'),

            'stats' => [
                'total_pending' =>
                    $allRows->count(),

                'supplier_bills' =>
                    $this->queueStats(
                        $allRows,
                        'supplier_bills'
                    ),

                'supplier_payments' =>
                    $this->queueStats(
                        $allRows,
                        'supplier_payments'
                    ),

                'customer_payments' =>
                    $this->queueStats(
                        $allRows,
                        'customer_payments'
                    ),

                'staff_ledger' =>
                    $this->queueStats(
                        $allRows,
                        'staff_ledger'
                    ),

                'refunds' =>
                    $this->queueStats(
                        $allRows,
                        'refunds'
                    ),

                'filtered_count' =>
                    $filteredRows->count(),
            ],

            'queue_rows' =>
                $filteredRows->all(),
        ];
    }

    private function supplierBillRows(): Collection
    {
        if (
            ! Schema::hasTable('supplier_bills')
            || ! Schema::hasTable('suppliers')
            || ! Schema::hasColumn(
                'supplier_bills',
                'approval_status'
            )
        ) {
            return collect();
        }

        return DB::table('supplier_bills as bills')
            ->leftJoin(
                'suppliers as suppliers',
                'suppliers.id',
                '=',
                'bills.supplier_id'
            )
            ->where(
                'bills.approval_status',
                'Pending'
            )
            ->select([
                'bills.id',
                'bills.bill_no',
                'bills.bill_date',
                'bills.service_type',
                'bills.reference_no',
                'bills.description',
                'bills.total_amount',
                'bills.approval_status',
                'bills.created_at',

                'suppliers.name as supplier_name',
                'suppliers.company_name',
            ])
            ->get()
            ->map(function (object $record): array {
                $party = filled(
                    $record->company_name
                )
                    ? $record->company_name
                        . ' - '
                        . $record->supplier_name
                    : (
                        $record->supplier_name
                        ?: 'Supplier'
                    );

                return $this->makeRow(
                    id: (int) $record->id,
                    queueKey: 'supplier_bills',
                    queueLabel: 'Supplier Bill',
                    sourceNo: $record->bill_no
                        ?: 'Supplier Bill #' . $record->id,
                    party: $party,
                    date: $record->bill_date
                        ?: $record->created_at,
                    amount: (float) $record->total_amount,
                    status: $record->approval_status
                        ?: 'Pending',
                    category: $record->service_type
                        ?: 'Other',
                    description:
                        $record->description
                        ?: $record->reference_no
                        ?: 'Supplier bill approval',
                    url: $this->resourceRecordUrl(
                        resourceSlug: 'supplier-bills',
                        recordId: (int) $record->id,
                    ),
                );
            });
    }

    private function supplierPaymentRows(): Collection
    {
        if (
            ! Schema::hasTable('supplier_payments')
            || ! Schema::hasTable('suppliers')
            || ! Schema::hasTable('supplier_bills')
        ) {
            return collect();
        }

        return DB::table(
            'supplier_payments as payments'
        )
            ->leftJoin(
                'suppliers as suppliers',
                'suppliers.id',
                '=',
                'payments.supplier_id'
            )
            ->leftJoin(
                'supplier_bills as bills',
                'bills.id',
                '=',
                'payments.supplier_bill_id'
            )
            ->where('payments.status', 'Pending')
            ->select([
                'payments.id',
                'payments.payment_no',
                'payments.payment_date',
                'payments.amount',
                'payments.payment_method',
                'payments.reference_no',
                'payments.status',
                'payments.created_at',

                'suppliers.name as supplier_name',
                'suppliers.company_name',

                'bills.bill_no',
            ])
            ->get()
            ->map(function (object $record): array {
                $party = filled(
                    $record->company_name
                )
                    ? $record->company_name
                        . ' - '
                        . $record->supplier_name
                    : (
                        $record->supplier_name
                        ?: 'Supplier'
                    );

                $description = 'Against bill: '
                    . (
                        $record->bill_no
                        ?: 'Not Available'
                    );

                if (filled($record->reference_no)) {
                    $description .= ' | Ref: '
                        . $record->reference_no;
                }

                return $this->makeRow(
                    id: (int) $record->id,
                    queueKey: 'supplier_payments',
                    queueLabel: 'Supplier Payment',
                    sourceNo: $record->payment_no
                        ?: 'Supplier Payment #'
                            . $record->id,
                    party: $party,
                    date: $record->payment_date
                        ?: $record->created_at,
                    amount: (float) $record->amount,
                    status: $record->status
                        ?: 'Pending',
                    category: $record->payment_method
                        ?: 'Payment',
                    description: $description,
                    url: $this->resourceRecordUrl(
                        resourceSlug:
                            'supplier-payments',
                        recordId: (int) $record->id,
                    ),
                );
            });
    }

    private function customerPaymentUploadRows(): Collection
    {
        if (
            ! Schema::hasTable(
                'customer_payment_uploads'
            )
            || ! Schema::hasTable('customers')
        ) {
            return collect();
        }

        return DB::table(
            'customer_payment_uploads as uploads'
        )
            ->leftJoin(
                'customers as customers',
                'customers.id',
                '=',
                'uploads.customer_id'
            )
            ->where('uploads.status', 'Pending')
            ->select([
                'uploads.id',
                'uploads.upload_no',
                'uploads.payment_date',
                'uploads.amount',
                'uploads.payment_method',
                'uploads.service_type',
                'uploads.reference_no',
                'uploads.status',
                'uploads.created_at',

                'customers.name as customer_name',
                'customers.customer_type',
            ])
            ->get()
            ->map(function (object $record): array {
                $party = $record->customer_name
                    ?: 'Customer';

                if (filled($record->customer_type)) {
                    $party .= ' ('
                        . $record->customer_type
                        . ')';
                }

                $description = $record->service_type
                    ?: 'Customer Receipt';

                if (filled($record->reference_no)) {
                    $description .= ' | Ref: '
                        . $record->reference_no;
                }

                return $this->makeRow(
                    id: (int) $record->id,
                    queueKey: 'customer_payments',
                    queueLabel:
                        'Customer Payment Upload',
                    sourceNo: $record->upload_no
                        ?: 'Payment Upload #'
                            . $record->id,
                    party: $party,
                    date: $record->payment_date
                        ?: $record->created_at,
                    amount: (float) $record->amount,
                    status: $record->status
                        ?: 'Pending',
                    category: $record->payment_method
                        ?: 'Receipt',
                    description: $description,
                    url: $this->resourceRecordUrl(
                        resourceSlug:
                            'customer-payment-uploads',
                        recordId: (int) $record->id,
                    ),
                );
            });
    }

    private function staffLedgerRows(): Collection
    {
        if (
            ! Schema::hasTable(
                'staff_ledger_entries'
            )
            || ! Schema::hasTable('staff')
        ) {
            return collect();
        }

        return DB::table(
            'staff_ledger_entries as entries'
        )
            ->leftJoin(
                'staff as staff',
                'staff.id',
                '=',
                'entries.staff_id'
            )
            ->where('entries.status', 'Pending')
            ->select([
                'entries.id',
                'entries.entry_no',
                'entries.entry_date',
                'entries.category',
                'entries.reference_no',
                'entries.description',
                'entries.debit',
                'entries.credit',
                'entries.status',
                'entries.created_at',

                'staff.name as staff_name',
            ])
            ->get()
            ->map(function (object $record): array {
                $amount = max(
                    (float) $record->debit,
                    (float) $record->credit
                );

                $entrySide =
                    (float) $record->debit > 0
                        ? 'Debit'
                        : 'Credit';

                $description = $entrySide;

                if (filled($record->description)) {
                    $description .= ' | '
                        . $record->description;
                } elseif (
                    filled($record->reference_no)
                ) {
                    $description .= ' | Ref: '
                        . $record->reference_no;
                }

                return $this->makeRow(
                    id: (int) $record->id,
                    queueKey: 'staff_ledger',
                    queueLabel: 'Staff Ledger',
                    sourceNo: $record->entry_no
                        ?: 'Staff Ledger #'
                            . $record->id,
                    party: $record->staff_name
                        ?: 'Staff Member',
                    date: $record->entry_date
                        ?: $record->created_at,
                    amount: $amount,
                    status: $record->status
                        ?: 'Pending',
                    category: $record->category
                        ?: 'Staff Entry',
                    description: $description,
                    url: $this->resourceRecordUrl(
                        resourceSlug:
                            'staff-ledger-entries',
                        recordId: (int) $record->id,
                    ),
                );
            });
    }

    private function refundRows(): Collection
    {
        if (
            ! Schema::hasTable('refunds')
            || ! Schema::hasTable('customers')
        ) {
            return collect();
        }

        return DB::table('refunds as refunds')
            ->leftJoin(
                'customers as customers',
                'customers.id',
                '=',
                'refunds.customer_id'
            )
            ->where('refunds.status', 'Pending')
            ->select([
                'refunds.id',
                'refunds.refund_no',
                'refunds.refund_date',
                'refunds.service_type',
                'refunds.service_reference',
                'refunds.refund_amount',
                'refunds.reason',
                'refunds.status',
                'refunds.created_at',

                'customers.name as customer_name',
                'customers.customer_type',
            ])
            ->get()
            ->map(function (object $record): array {
                $party = $record->customer_name
                    ?: 'Customer';

                if (filled($record->customer_type)) {
                    $party .= ' ('
                        . $record->customer_type
                        . ')';
                }

                $description = $record->reason
                    ?: 'Customer refund';

                if (
                    filled(
                        $record->service_reference
                    )
                ) {
                    $description .= ' | Ref: '
                        . $record->service_reference;
                }

                return $this->makeRow(
                    id: (int) $record->id,
                    queueKey: 'refunds',
                    queueLabel: 'Customer Refund',
                    sourceNo: $record->refund_no
                        ?: 'Refund #' . $record->id,
                    party: $party,
                    date: $record->refund_date
                        ?: $record->created_at,
                    amount:
                        (float) $record->refund_amount,
                    status: $record->status
                        ?: 'Pending',
                    category: $record->service_type
                        ?: 'Other',
                    description: $description,
                    url: $this->resourceRecordUrl(
                        resourceSlug: 'refunds',
                        recordId: (int) $record->id,
                    ),
                );
            });
    }

    private function makeRow(
        int $id,
        string $queueKey,
        string $queueLabel,
        string $sourceNo,
        string $party,
        mixed $date,
        float $amount,
        string $status,
        string $category,
        string $description,
        string $url,
    ): array {
        $recordDate = $this->normalizeDate($date);

        $ageDays = max(
            0,
            (int) $recordDate
                ->copy()
                ->startOfDay()
                ->diffInDays(today())
        );

        $priority = $this->priority($ageDays);

        $searchText = implode(' ', [
            $queueLabel,
            $sourceNo,
            $party,
            $category,
            $description,
            $status,
        ]);

        return [
            'id' => $id,
            'queue_key' => $queueKey,
            'queue_label' => $queueLabel,
            'source_no' => $sourceNo,
            'party' => $party,

            'date' =>
                $recordDate->format('d M Y'),

            'date_value' =>
                $recordDate->format('Y-m-d'),

            'age_days' => $ageDays,

            'amount' => round(
                max(0, $amount),
                2
            ),

            'status' => $status,
            'category' => $category,

            'description' => Str::limit(
                trim($description),
                120
            ),

            'priority' =>
                $priority['label'],

            'priority_key' =>
                $priority['key'],

            'url' => $url,
            'search_text' => $searchText,
        ];
    }

    private function queueStats(
        Collection $rows,
        string $queueKey,
    ): array {
        $queueRows = $rows->where(
            'queue_key',
            $queueKey
        );

        return [
            'count' => $queueRows->count(),

            'amount' => round(
                (float) $queueRows->sum('amount'),
                2
            ),
        ];
    }

    private function priority(int $ageDays): array
    {
        return match (true) {
            $ageDays >= 30 => [
                'key' => 'critical',
                'label' => 'Critical',
            ],

            $ageDays >= 14 => [
                'key' => 'high',
                'label' => 'High',
            ],

            $ageDays >= 7 => [
                'key' => 'medium',
                'label' => 'Medium',
            ],

            default => [
                'key' => 'normal',
                'label' => 'Normal',
            ],
        };
    }

    private function normalizeDate(mixed $value): Carbon
    {
        try {
            return filled($value)
                ? Carbon::parse($value)
                : now();
        } catch (Throwable) {
            return now();
        }
    }

    private function resourceRecordUrl(
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
                // Try next supported route.
            }
        }

        $indexRoute =
            $routeBase . '.index';

        if (Route::has($indexRoute)) {
            try {
                return route($indexRoute);
            } catch (Throwable) {
                // Use fallback URL.
            }
        }

        return url(
            '/admin/' . $resourceSlug
        );
    }
}