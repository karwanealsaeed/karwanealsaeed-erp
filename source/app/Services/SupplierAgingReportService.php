<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SupplierAgingReportService
{
    public function generate(
        int|string|null $supplierId = null,
        ?string $supplierType = null,
        ?string $serviceType = null,
    ): array {
        $agingDate = today()->startOfDay();

        if (
            ! Schema::hasTable('supplier_bills')
            || ! Schema::hasTable('suppliers')
        ) {
            return $this->emptyReport($agingDate);
        }

        $records = DB::table('supplier_bills as bills')
            ->join(
                'suppliers as suppliers',
                'suppliers.id',
                '=',
                'bills.supplier_id'
            )
            ->select([
                'bills.id',
                'bills.bill_no',
                'bills.supplier_id',
                'bills.supplier_invoice_no',
                'bills.bill_date',
                'bills.due_date',
                'bills.service_type',
                'bills.reference_no',
                'bills.description',
                'bills.total_amount',
                'bills.paid_amount',
                'bills.balance_amount',
                'bills.status',

                'suppliers.name as supplier_name',
                'suppliers.company_name',
                'suppliers.mobile as supplier_mobile',
                'suppliers.supplier_type',
                'suppliers.status as supplier_status',
            ])
            ->whereIn('bills.status', [
                'Unpaid',
                'Partially Paid',
            ])
            ->where('bills.balance_amount', '>', 0)
            ->when(
                filled($supplierId),
                fn ($query) => $query->where(
                    'bills.supplier_id',
                    (int) $supplierId
                )
            )
            ->when(
                filled($supplierType),
                fn ($query) => $query->where(
                    'suppliers.supplier_type',
                    $supplierType
                )
            )
            ->when(
                filled($serviceType),
                fn ($query) => $query->where(
                    'bills.service_type',
                    $serviceType
                )
            )
            ->orderByRaw(
                'COALESCE(bills.due_date, bills.bill_date) ASC'
            )
            ->orderBy('bills.bill_date')
            ->get();

        $billRows = $records
            ->map(
                fn (object $record): array =>
                    $this->makeBillRow(
                        $record,
                        $agingDate
                    )
            )
            ->sortByDesc('days_overdue')
            ->values();

        return [
            'aging_date' => $agingDate->format('d M Y'),

            'stats' => $this->makeStats($billRows),

            'supplier_rows' =>
                $this->makeSupplierRows($billRows),

            'bill_rows' => $billRows->all(),
        ];
    }

    private function makeBillRow(
        object $record,
        Carbon $agingDate,
    ): array {
        /*
         * Due date missing ho to bill date aging ke liye
         * fallback hogi.
         */
        $dueDate = Carbon::parse(
            $record->due_date
                ?: $record->bill_date
        )->startOfDay();

        $daysOverdue = $dueDate->lt($agingDate)
            ? (int) $dueDate->diffInDays($agingDate)
            : 0;

        $bucket = $this->resolveBucket(
            dueDate: $dueDate,
            agingDate: $agingDate,
            daysOverdue: $daysOverdue,
        );

        $balance = round(
            (float) $record->balance_amount,
            2
        );

        $supplierDisplayName = filled(
            $record->company_name
        )
            ? $record->company_name
                . ' - '
                . $record->supplier_name
            : $record->supplier_name;

        return [
            'id' => (int) $record->id,

            'bill_no' =>
                (string) $record->bill_no,

            'supplier_id' =>
                (int) $record->supplier_id,

            'supplier_name' =>
                (string) $supplierDisplayName,

            'contact_name' =>
                (string) $record->supplier_name,

            'company_name' =>
                (string) ($record->company_name ?? ''),

            'supplier_mobile' =>
                (string) ($record->supplier_mobile ?? ''),

            'supplier_type' =>
                (string) ($record->supplier_type ?? 'Other'),

            'supplier_invoice_no' =>
                (string) (
                    $record->supplier_invoice_no ?? ''
                ),

            'bill_date' => Carbon::parse(
                $record->bill_date
            )->format('d M Y'),

            'bill_date_value' => Carbon::parse(
                $record->bill_date
            )->format('Y-m-d'),

            'due_date' => $dueDate->format('d M Y'),

            'due_date_value' => $dueDate->format('Y-m-d'),

            'service_type' =>
                (string) $record->service_type,

            'reference_no' =>
                (string) ($record->reference_no ?? ''),

            'description' =>
                (string) ($record->description ?? ''),

            'total_amount' => round(
                (float) $record->total_amount,
                2
            ),

            'paid_amount' => round(
                (float) $record->paid_amount,
                2
            ),

            'balance_amount' => $balance,

            'status' => (string) $record->status,

            'days_overdue' => $daysOverdue,

            'bucket' => $bucket,

            'current_amount' =>
                $bucket === 'Current'
                    ? $balance
                    : 0,

            'days_1_30_amount' =>
                $bucket === '1-30 Days'
                    ? $balance
                    : 0,

            'days_31_60_amount' =>
                $bucket === '31-60 Days'
                    ? $balance
                    : 0,

            'days_61_90_amount' =>
                $bucket === '61-90 Days'
                    ? $balance
                    : 0,

            'days_90_plus_amount' =>
                $bucket === '90+ Days'
                    ? $balance
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
        Collection $billRows,
    ): array {
        return [
            'supplier_count' => $billRows
                ->pluck('supplier_id')
                ->unique()
                ->count(),

            'bill_count' => $billRows->count(),

            'total_outstanding' => round(
                (float) $billRows->sum(
                    'balance_amount'
                ),
                2
            ),

            'current' => round(
                (float) $billRows->sum(
                    'current_amount'
                ),
                2
            ),

            'days_1_30' => round(
                (float) $billRows->sum(
                    'days_1_30_amount'
                ),
                2
            ),

            'days_31_60' => round(
                (float) $billRows->sum(
                    'days_31_60_amount'
                ),
                2
            ),

            'days_61_90' => round(
                (float) $billRows->sum(
                    'days_61_90_amount'
                ),
                2
            ),

            'days_90_plus' => round(
                (float) $billRows->sum(
                    'days_90_plus_amount'
                ),
                2
            ),
        ];
    }

    private function makeSupplierRows(
        Collection $billRows,
    ): array {
        return $billRows
            ->groupBy('supplier_id')
            ->map(function (
                Collection $supplierBills
            ): array {
                $first = $supplierBills->first();

                $oldestBill = $supplierBills
                    ->sortBy('due_date_value')
                    ->first();

                return [
                    'supplier_id' =>
                        $first['supplier_id'],

                    'supplier_name' =>
                        $first['supplier_name'],

                    'supplier_mobile' =>
                        $first['supplier_mobile'],

                    'supplier_type' =>
                        $first['supplier_type'],

                    'bill_count' =>
                        $supplierBills->count(),

                    'oldest_due_date' =>
                        $oldestBill['due_date'],

                    'maximum_days_overdue' =>
                        (int) $supplierBills->max(
                            'days_overdue'
                        ),

                    'current' => round(
                        (float) $supplierBills->sum(
                            'current_amount'
                        ),
                        2
                    ),

                    'days_1_30' => round(
                        (float) $supplierBills->sum(
                            'days_1_30_amount'
                        ),
                        2
                    ),

                    'days_31_60' => round(
                        (float) $supplierBills->sum(
                            'days_31_60_amount'
                        ),
                        2
                    ),

                    'days_61_90' => round(
                        (float) $supplierBills->sum(
                            'days_61_90_amount'
                        ),
                        2
                    ),

                    'days_90_plus' => round(
                        (float) $supplierBills->sum(
                            'days_90_plus_amount'
                        ),
                        2
                    ),

                    'total_outstanding' => round(
                        (float) $supplierBills->sum(
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
                'supplier_count' => 0,
                'bill_count' => 0,
                'total_outstanding' => 0,
                'current' => 0,
                'days_1_30' => 0,
                'days_31_60' => 0,
                'days_61_90' => 0,
                'days_90_plus' => 0,
            ],

            'supplier_rows' => [],
            'bill_rows' => [],
        ];
    }
}