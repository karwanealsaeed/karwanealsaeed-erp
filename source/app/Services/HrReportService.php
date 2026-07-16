<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Staff;
use App\Models\StaffDocument;
use App\Models\StaffLeave;
use App\Models\StaffLedgerEntry;
use App\Models\StaffPerformanceNote;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Throwable;

class HrReportService
{
    public function generate(
        ?string $dateFrom = null,
        ?string $dateTo = null,
        int|string|null $departmentId = null,
        int|string|null $staffId = null,
    ): array {
        $from = $this->normalizeDate(
            $dateFrom,
            now()->startOfMonth()
        )->startOfDay();

        $to = $this->normalizeDate(
            $dateTo,
            now()->endOfMonth()
        )->endOfDay();

        if ($to->lessThan($from)) {
            [$from, $to] = [
                $to->copy()->startOfDay(),
                $from->copy()->endOfDay(),
            ];
        }

        if (! Schema::hasTable('staff')) {
            return $this->emptyReport($from, $to);
        }

        $staffQuery = Staff::query()
            ->with([
                'department:id,name',
                'designation:id,name',
            ]);

        if (filled($departmentId)) {
            $staffQuery->where(
                'department_id',
                (int) $departmentId
            );
        }

        if (filled($staffId)) {
            $staffQuery->whereKey((int) $staffId);
        }

        $staffMembers = $staffQuery
            ->orderBy('name')
            ->get();

        $staffIds = $staffMembers
            ->pluck('id')
            ->values();

        $leaves = $this->getLeaves(
            $staffIds,
            $from,
            $to
        );

        $ledgerEntries = $this->getLedgerEntries(
            $staffIds,
            $from,
            $to
        );

        $documents = $this->getExpiringDocuments(
            $staffIds
        );

        $performanceNotes = $this->getPerformanceNotes(
            $staffIds,
            $from,
            $to
        );

        $approvedLeaves = $leaves
            ->where('status', 'Approved');

        $pendingLeaves = $leaves
            ->where('status', 'Pending');

        $approvedLedger = $ledgerEntries
            ->where('status', 'Approved');

        $finalPerformanceNotes = $performanceNotes
            ->where('status', 'Final');

        $totalDebit = round(
            (float) $approvedLedger->sum('debit'),
            2
        );

        $totalCredit = round(
            (float) $approvedLedger->sum('credit'),
            2
        );

        $averageRating = $finalPerformanceNotes
            ->whereNotNull('rating')
            ->avg('rating');

        return [
            'period' => [
                'from' => $from->format('d M Y'),
                'to' => $to->format('d M Y'),
                'from_value' => $from->format('Y-m-d'),
                'to_value' => $to->format('Y-m-d'),
            ],

            'stats' => [
                'total_staff' => $staffMembers->count(),

                'active_staff' => $staffMembers
                    ->where('status', 'Active')
                    ->count(),

                'new_joiners' => $staffMembers
                    ->filter(
                        fn (Staff $staff): bool =>
                            $staff->joining_date
                            && $staff->joining_date->gte($from)
                            && $staff->joining_date->lte($to)
                    )
                    ->count(),

                'pending_leaves' => $pendingLeaves->count(),

                'approved_leave_days' =>
                    $this->calculateLeaveDays(
                        $approvedLeaves,
                        $from,
                        $to
                    ),

                'expiring_documents' => $documents->count(),

                'final_reviews' => $finalPerformanceNotes->count(),

                'average_rating' => $averageRating !== null
                    ? round((float) $averageRating, 1)
                    : 0,

                'ledger_debit' => $totalDebit,
                'ledger_credit' => $totalCredit,
                'ledger_balance' => round(
                    $totalDebit - $totalCredit,
                    2
                ),
            ],

            'department_rows' => $this->departmentRows(
                staffMembers: $staffMembers,
                leaves: $leaves,
                ledgerEntries: $ledgerEntries,
                documents: $documents,
                performanceNotes: $performanceNotes,
                from: $from,
                to: $to,
                selectedDepartmentId: filled($departmentId)
                    ? (int) $departmentId
                    : null,
            ),

            'staff_rows' => $this->staffRows(
                staffMembers: $staffMembers,
                leaves: $leaves,
                ledgerEntries: $ledgerEntries,
                documents: $documents,
                performanceNotes: $performanceNotes,
                from: $from,
                to: $to,
            ),
        ];
    }

    private function getLeaves(
        Collection $staffIds,
        Carbon $from,
        Carbon $to,
    ): Collection {
        if (
            $staffIds->isEmpty()
            || ! Schema::hasTable('staff_leaves')
        ) {
            return collect();
        }

        return StaffLeave::query()
            ->whereIn('staff_id', $staffIds)
            ->whereDate('start_date', '<=', $to)
            ->whereDate('end_date', '>=', $from)
            ->get();
    }

    private function getLedgerEntries(
        Collection $staffIds,
        Carbon $from,
        Carbon $to,
    ): Collection {
        if (
            $staffIds->isEmpty()
            || ! Schema::hasTable('staff_ledger_entries')
        ) {
            return collect();
        }

        return StaffLedgerEntry::query()
            ->whereIn('staff_id', $staffIds)
            ->whereBetween('entry_date', [
                $from->format('Y-m-d'),
                $to->format('Y-m-d'),
            ])
            ->get();
    }

    private function getExpiringDocuments(
        Collection $staffIds
    ): Collection {
        if (
            $staffIds->isEmpty()
            || ! Schema::hasTable('staff_documents')
        ) {
            return collect();
        }

        return StaffDocument::query()
            ->whereIn('staff_id', $staffIds)
            ->whereNotNull('expiry_date')
            ->where('status', '!=', 'Archived')
            ->whereBetween('expiry_date', [
                today()->format('Y-m-d'),
                today()->addDays(30)->format('Y-m-d'),
            ])
            ->get();
    }

    private function getPerformanceNotes(
        Collection $staffIds,
        Carbon $from,
        Carbon $to,
    ): Collection {
        if (
            $staffIds->isEmpty()
            || ! Schema::hasTable('staff_performance_notes')
        ) {
            return collect();
        }

        return StaffPerformanceNote::query()
            ->whereIn('staff_id', $staffIds)
            ->whereBetween('review_date', [
                $from->format('Y-m-d'),
                $to->format('Y-m-d'),
            ])
            ->get();
    }

    private function departmentRows(
        Collection $staffMembers,
        Collection $leaves,
        Collection $ledgerEntries,
        Collection $documents,
        Collection $performanceNotes,
        Carbon $from,
        Carbon $to,
        ?int $selectedDepartmentId,
    ): array {
        if (! Schema::hasTable('departments')) {
            return [];
        }

        $departmentQuery = Department::query()
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($selectedDepartmentId) {
            $departmentQuery->whereKey(
                $selectedDepartmentId
            );
        }

        $departments = $departmentQuery->get();

        $rows = $departments
            ->map(function (Department $department) use (
                $staffMembers,
                $leaves,
                $ledgerEntries,
                $documents,
                $performanceNotes,
                $from,
                $to
            ): array {
                $departmentStaff = $staffMembers
                    ->where(
                        'department_id',
                        $department->id
                    );

                return $this->makeDepartmentRow(
                    name: $department->name,
                    departmentStaff: $departmentStaff,
                    leaves: $leaves,
                    ledgerEntries: $ledgerEntries,
                    documents: $documents,
                    performanceNotes: $performanceNotes,
                    from: $from,
                    to: $to,
                );
            })
            ->values();

        $unassignedStaff = $staffMembers
            ->whereNull('department_id');

        if (
            ! $selectedDepartmentId
            && $unassignedStaff->isNotEmpty()
        ) {
            $rows->push(
                $this->makeDepartmentRow(
                    name: 'Unassigned',
                    departmentStaff: $unassignedStaff,
                    leaves: $leaves,
                    ledgerEntries: $ledgerEntries,
                    documents: $documents,
                    performanceNotes: $performanceNotes,
                    from: $from,
                    to: $to,
                )
            );
        }

        return $rows->all();
    }

    private function makeDepartmentRow(
        string $name,
        Collection $departmentStaff,
        Collection $leaves,
        Collection $ledgerEntries,
        Collection $documents,
        Collection $performanceNotes,
        Carbon $from,
        Carbon $to,
    ): array {
        $staffIds = $departmentStaff->pluck('id');

        $departmentLeaves = $leaves
            ->whereIn('staff_id', $staffIds);

        $departmentLedger = $ledgerEntries
            ->whereIn('staff_id', $staffIds)
            ->where('status', 'Approved');

        $departmentDocuments = $documents
            ->whereIn('staff_id', $staffIds);

        $departmentPerformance = $performanceNotes
            ->whereIn('staff_id', $staffIds)
            ->where('status', 'Final')
            ->whereNotNull('rating');

        $debit = round(
            (float) $departmentLedger->sum('debit'),
            2
        );

        $credit = round(
            (float) $departmentLedger->sum('credit'),
            2
        );

        $averageRating = $departmentPerformance
            ->avg('rating');

        return [
            'department' => $name,

            'staff_count' => $departmentStaff->count(),

            'active_staff' => $departmentStaff
                ->where('status', 'Active')
                ->count(),

            'pending_leaves' => $departmentLeaves
                ->where('status', 'Pending')
                ->count(),

            'approved_leave_days' =>
                $this->calculateLeaveDays(
                    $departmentLeaves->where(
                        'status',
                        'Approved'
                    ),
                    $from,
                    $to
                ),

            'expiring_documents' =>
                $departmentDocuments->count(),

            'average_rating' => $averageRating !== null
                ? round((float) $averageRating, 1)
                : 0,

            'ledger_debit' => $debit,
            'ledger_credit' => $credit,
            'ledger_balance' => round(
                $debit - $credit,
                2
            ),
        ];
    }

    private function staffRows(
        Collection $staffMembers,
        Collection $leaves,
        Collection $ledgerEntries,
        Collection $documents,
        Collection $performanceNotes,
        Carbon $from,
        Carbon $to,
    ): array {
        return $staffMembers
            ->map(function (Staff $staff) use (
                $leaves,
                $ledgerEntries,
                $documents,
                $performanceNotes,
                $from,
                $to
            ): array {
                $staffLeaves = $leaves
                    ->where('staff_id', $staff->id);

                $staffLedger = $ledgerEntries
                    ->where('staff_id', $staff->id)
                    ->where('status', 'Approved');

                $staffDocuments = $documents
                    ->where('staff_id', $staff->id);

                $staffPerformance = $performanceNotes
                    ->where('staff_id', $staff->id)
                    ->where('status', 'Final')
                    ->whereNotNull('rating');

                $debit = round(
                    (float) $staffLedger->sum('debit'),
                    2
                );

                $credit = round(
                    (float) $staffLedger->sum('credit'),
                    2
                );

                $averageRating = $staffPerformance
                    ->avg('rating');

                return [
                    'id' => $staff->id,
                    'staff_code' =>
                        $staff->staff_code
                        ?: $staff->employee_code
                        ?: '-',

                    'name' => $staff->name,

                    'department' =>
                        $staff->department?->name
                        ?: 'Unassigned',

                    'designation' =>
                        $staff->designation?->name
                        ?: '-',

                    'joining_date' =>
                        $staff->joining_date?->format(
                            'd M Y'
                        ) ?: '-',

                    'status' => $staff->status ?: '-',

                    'basic_salary' => round(
                        (float) $staff->basic_salary,
                        2
                    ),

                    'pending_leaves' => $staffLeaves
                        ->where('status', 'Pending')
                        ->count(),

                    'approved_leave_days' =>
                        $this->calculateLeaveDays(
                            $staffLeaves->where(
                                'status',
                                'Approved'
                            ),
                            $from,
                            $to
                        ),

                    'expiring_documents' =>
                        $staffDocuments->count(),

                    'average_rating' =>
                        $averageRating !== null
                            ? round(
                                (float) $averageRating,
                                1
                            )
                            : 0,

                    'ledger_debit' => $debit,
                    'ledger_credit' => $credit,
                    'ledger_balance' => round(
                        $debit - $credit,
                        2
                    ),
                ];
            })
            ->values()
            ->all();
    }

    private function calculateLeaveDays(
        Collection $leaves,
        Carbon $from,
        Carbon $to,
    ): float {
        $total = $leaves->sum(
            function (StaffLeave $leave) use (
                $from,
                $to
            ): float {
                $leaveStart = Carbon::parse(
                    $leave->start_date
                )->startOfDay();

                $leaveEnd = Carbon::parse(
                    $leave->end_date
                )->startOfDay();

                $overlapStart = $leaveStart->greaterThan(
                    $from
                )
                    ? $leaveStart
                    : $from->copy()->startOfDay();

                $overlapEnd = $leaveEnd->lessThan($to)
                    ? $leaveEnd
                    : $to->copy()->startOfDay();

                if ($overlapEnd->lessThan($overlapStart)) {
                    return 0;
                }

                if (
                    $leaveStart->isSameDay($leaveEnd)
                    && (float) $leave->total_days < 1
                ) {
                    return (float) $leave->total_days;
                }

                return (float) (
                    $overlapStart->diffInDays(
                        $overlapEnd
                    ) + 1
                );
            }
        );

        return round((float) $total, 2);
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
    ): array {
        return [
            'period' => [
                'from' => $from->format('d M Y'),
                'to' => $to->format('d M Y'),
                'from_value' => $from->format('Y-m-d'),
                'to_value' => $to->format('Y-m-d'),
            ],

            'stats' => [
                'total_staff' => 0,
                'active_staff' => 0,
                'new_joiners' => 0,
                'pending_leaves' => 0,
                'approved_leave_days' => 0,
                'expiring_documents' => 0,
                'final_reviews' => 0,
                'average_rating' => 0,
                'ledger_debit' => 0,
                'ledger_credit' => 0,
                'ledger_balance' => 0,
            ],

            'department_rows' => [],
            'staff_rows' => [],
        ];
    }
}