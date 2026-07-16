<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CustomerAgingReportService
{
    public function generate(
        int|string|null $customerId = null,
        ?string $customerType = null,
        ?string $serviceType = null,
    ): array {
        $agingDate = today()->startOfDay();

        if (
            ! Schema::hasTable('invoices')
            || ! Schema::hasTable('customers')
        ) {
            return $this->emptyReport($agingDate);
        }

        $records = DB::table('invoices as invoices')
            ->join(
                'customers as customers',
                'customers.id',
                '=',
                'invoices.customer_id'
            )
            ->select([
                'invoices.id',
                'invoices.invoice_no',
                'invoices.customer_id',
                'invoices.invoice_date',
                'invoices.due_date',
                'invoices.service_type',
                'invoices.service_reference',
                'invoices.total_amount',
                'invoices.paid_amount',
                'invoices.balance_amount',
                'invoices.status',

                'customers.name as customer_name',
                'customers.mobile as customer_mobile',
                'customers.customer_type',
                'customers.status as customer_status',
            ])
            ->whereNotIn('invoices.status', [
                'Draft',
                'Paid',
                'Cancelled',
            ])
            ->where('invoices.balance_amount', '>', 0)
            ->when(
                filled($customerId),
                fn ($query) => $query->where(
                    'invoices.customer_id',
                    (int) $customerId
                )
            )
            ->when(
                filled($customerType),
                fn ($query) => $query->where(
                    'customers.customer_type',
                    $customerType
                )
            )
            ->when(
                filled($serviceType),
                fn ($query) => $query->where(
                    'invoices.service_type',
                    $serviceType
                )
            )
            ->orderBy('invoices.due_date')
            ->orderBy('invoices.invoice_date')
            ->get();

        $invoiceRows = $records
            ->map(
                fn (object $record): array =>
                    $this->makeInvoiceRow(
                        $record,
                        $agingDate
                    )
            )
            ->sortByDesc('days_overdue')
            ->values();

        return [
            'aging_date' => $agingDate->format('d M Y'),

            'stats' => $this->makeStats($invoiceRows),

            'customer_rows' =>
                $this->makeCustomerRows($invoiceRows),

            'invoice_rows' => $invoiceRows->all(),
        ];
    }

    private function makeInvoiceRow(
        object $record,
        Carbon $agingDate,
    ): array {
        /*
         * Due date na ho to invoice date aging ke liye
         * fallback hogi.
         */
        $dueDate = Carbon::parse(
            $record->due_date
                ?: $record->invoice_date
        )->startOfDay();

        $daysOverdue = $dueDate->lt($agingDate)
            ? (int) $dueDate->diffInDays($agingDate)
            : 0;

        $bucket = $this->resolveBucket(
            $dueDate,
            $agingDate,
            $daysOverdue
        );

        return [
            'id' => (int) $record->id,

            'invoice_no' =>
                (string) $record->invoice_no,

            'customer_id' =>
                (int) $record->customer_id,

            'customer_name' =>
                (string) $record->customer_name,

            'customer_mobile' =>
                (string) ($record->customer_mobile ?? ''),

            'customer_type' =>
                (string) ($record->customer_type ?? 'Customer'),

            'invoice_date' => Carbon::parse(
                $record->invoice_date
            )->format('d M Y'),

            'invoice_date_value' => Carbon::parse(
                $record->invoice_date
            )->format('Y-m-d'),

            'due_date' => $dueDate->format('d M Y'),

            'due_date_value' => $dueDate->format('Y-m-d'),

            'service_type' =>
                (string) $record->service_type,

            'service_reference' =>
                (string) ($record->service_reference ?? ''),

            'total_amount' => round(
                (float) $record->total_amount,
                2
            ),

            'paid_amount' => round(
                (float) $record->paid_amount,
                2
            ),

            'balance_amount' => round(
                (float) $record->balance_amount,
                2
            ),

            'status' => (string) $record->status,

            'days_overdue' => $daysOverdue,

            'bucket' => $bucket,

            'current_amount' =>
                $bucket === 'Current'
                    ? round(
                        (float) $record->balance_amount,
                        2
                    )
                    : 0,

            'days_1_30_amount' =>
                $bucket === '1-30 Days'
                    ? round(
                        (float) $record->balance_amount,
                        2
                    )
                    : 0,

            'days_31_60_amount' =>
                $bucket === '31-60 Days'
                    ? round(
                        (float) $record->balance_amount,
                        2
                    )
                    : 0,

            'days_61_90_amount' =>
                $bucket === '61-90 Days'
                    ? round(
                        (float) $record->balance_amount,
                        2
                    )
                    : 0,

            'days_90_plus_amount' =>
                $bucket === '90+ Days'
                    ? round(
                        (float) $record->balance_amount,
                        2
                    )
                    : 0,
        ];
    }

    private function resolveBucket(
        Carbon $dueDate,
        Carbon $agingDate,
        int $daysOverdue,
    ): string {
        if ($dueDate->gte($agingDate)) {
            return 'Current';
        }

        if ($daysOverdue <= 30) {
            return '1-30 Days';
        }

        if ($daysOverdue <= 60) {
            return '31-60 Days';
        }

        if ($daysOverdue <= 90) {
            return '61-90 Days';
        }

        return '90+ Days';
    }

    private function makeStats(
        Collection $invoiceRows,
    ): array {
        return [
            'customer_count' => $invoiceRows
                ->pluck('customer_id')
                ->unique()
                ->count(),

            'invoice_count' => $invoiceRows->count(),

            'total_outstanding' => round(
                (float) $invoiceRows->sum(
                    'balance_amount'
                ),
                2
            ),

            'current' => round(
                (float) $invoiceRows->sum(
                    'current_amount'
                ),
                2
            ),

            'days_1_30' => round(
                (float) $invoiceRows->sum(
                    'days_1_30_amount'
                ),
                2
            ),

            'days_31_60' => round(
                (float) $invoiceRows->sum(
                    'days_31_60_amount'
                ),
                2
            ),

            'days_61_90' => round(
                (float) $invoiceRows->sum(
                    'days_61_90_amount'
                ),
                2
            ),

            'days_90_plus' => round(
                (float) $invoiceRows->sum(
                    'days_90_plus_amount'
                ),
                2
            ),
        ];
    }

    private function makeCustomerRows(
        Collection $invoiceRows,
    ): array {
        return $invoiceRows
            ->groupBy('customer_id')
            ->map(function (
                Collection $customerInvoices
            ): array {
                $first = $customerInvoices->first();

                $oldestInvoice = $customerInvoices
                    ->sortBy('due_date_value')
                    ->first();

                return [
                    'customer_id' =>
                        $first['customer_id'],

                    'customer_name' =>
                        $first['customer_name'],

                    'customer_mobile' =>
                        $first['customer_mobile'],

                    'customer_type' =>
                        $first['customer_type'],

                    'invoice_count' =>
                        $customerInvoices->count(),

                    'oldest_due_date' =>
                        $oldestInvoice['due_date'],

                    'maximum_days_overdue' =>
                        (int) $customerInvoices->max(
                            'days_overdue'
                        ),

                    'current' => round(
                        (float) $customerInvoices->sum(
                            'current_amount'
                        ),
                        2
                    ),

                    'days_1_30' => round(
                        (float) $customerInvoices->sum(
                            'days_1_30_amount'
                        ),
                        2
                    ),

                    'days_31_60' => round(
                        (float) $customerInvoices->sum(
                            'days_31_60_amount'
                        ),
                        2
                    ),

                    'days_61_90' => round(
                        (float) $customerInvoices->sum(
                            'days_61_90_amount'
                        ),
                        2
                    ),

                    'days_90_plus' => round(
                        (float) $customerInvoices->sum(
                            'days_90_plus_amount'
                        ),
                        2
                    ),

                    'total_outstanding' => round(
                        (float) $customerInvoices->sum(
                            'balance_amount'
                        ),
                        2
                    ),
                ];
            })
            ->sortByDesc('total_outstanding')
            ->values()
            ->all();
    }

    private function emptyReport(
        Carbon $agingDate,
    ): array {
        return [
            'aging_date' =>
                $agingDate->format('d M Y'),

            'stats' => [
                'customer_count' => 0,
                'invoice_count' => 0,
                'total_outstanding' => 0,
                'current' => 0,
                'days_1_30' => 0,
                'days_31_60' => 0,
                'days_61_90' => 0,
                'days_90_plus' => 0,
            ],

            'customer_rows' => [],
            'invoice_rows' => [],
        ];
    }
}