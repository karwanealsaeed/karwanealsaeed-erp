<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class AccountingPostingAuditService
{
    private const TOLERANCE = 0.01;

    public function generate(
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $voucherStatus = 'Posted',
        ?string $severity = 'all',
        ?string $issueType = 'all',
        ?string $search = null,
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

        if (! $this->requiredTablesExist()) {
            return $this->emptyReport($from, $to);
        }

        $vouchers = DB::table('account_vouchers')
            ->whereBetween('voucher_date', [
                $from->format('Y-m-d'),
                $to->format('Y-m-d'),
            ])
            ->when(
                filled($voucherStatus)
                && $voucherStatus !== 'all',
                fn ($query) => $query->where(
                    'status',
                    $voucherStatus
                )
            )
            ->orderBy('voucher_date')
            ->orderBy('id')
            ->get();

        if ($vouchers->isEmpty()) {
            return $this->emptyReport($from, $to);
        }

        $voucherIds = $vouchers
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->values();

        $lines = DB::table('account_voucher_lines')
            ->whereIn('account_voucher_id', $voucherIds)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $ledgers = DB::table('account_ledgers')
            ->whereIn('account_voucher_id', $voucherIds)
            ->orderBy('id')
            ->get();

        $linesByVoucher = $lines->groupBy(
            'account_voucher_id'
        );

        $ledgersByVoucher = $ledgers->groupBy(
            'account_voucher_id'
        );

        $issues = collect();

        foreach ($vouchers as $voucher) {
            $voucherLines = $linesByVoucher->get(
                $voucher->id,
                collect()
            );

            $voucherLedgers = $ledgersByVoucher->get(
                $voucher->id,
                collect()
            );

            $this->auditVoucherHeader(
                $voucher,
                $voucherLines,
                $issues
            );

            $this->auditVoucherLedgers(
                $voucher,
                $voucherLedgers,
                $issues
            );

            $this->auditLineLedgerLinks(
                $voucher,
                $voucherLines,
                $voucherLedgers,
                $issues
            );

            $this->auditLedgerStatuses(
                $voucher,
                $voucherLedgers,
                $issues
            );

            $this->auditSourceLink(
                $voucher,
                $issues
            );
        }

        $this->auditDuplicateSourcePostings(
            $vouchers,
            $issues
        );

        $allIssues = $issues
            ->sortBy(function (array $issue): string {
                $severitySort = match (
                    $issue['severity_key']
                ) {
                    'critical' => '1',
                    'high' => '2',
                    'medium' => '3',
                    default => '4',
                };

                return $severitySort
                    . '-'
                    . $issue['voucher_date_value']
                    . '-'
                    . str_pad(
                        (string) $issue['voucher_id'],
                        12,
                        '0',
                        STR_PAD_LEFT
                    );
            })
            ->values();

        $filteredIssues = $allIssues;

        if (
            filled($severity)
            && $severity !== 'all'
        ) {
            $filteredIssues = $filteredIssues
                ->where('severity_key', $severity)
                ->values();
        }

        if (
            filled($issueType)
            && $issueType !== 'all'
        ) {
            $filteredIssues = $filteredIssues
                ->where('issue_key', $issueType)
                ->values();
        }

        if (filled($search)) {
            $needle = Str::lower(
                trim((string) $search)
            );

            $filteredIssues = $filteredIssues
                ->filter(
                    fn (array $issue): bool =>
                        Str::contains(
                            Str::lower(
                                $issue['search_text']
                            ),
                            $needle
                        )
                )
                ->values();
        }

        $voucherMap = $vouchers->keyBy('id');

        $affectedVoucherIds = $allIssues
            ->pluck('voucher_id')
            ->unique()
            ->values();

        $filteredAffectedIds = $filteredIssues
            ->pluck('voucher_id')
            ->unique()
            ->values();

        $passedVouchers = max(
            0,
            $vouchers->count()
            - $affectedVoucherIds->count()
        );

        $integrityScore = $vouchers->isEmpty()
            ? 100
            : round(
                (
                    $passedVouchers
                    / $vouchers->count()
                ) * 100,
                1
            );

        $affectedAmount = $filteredAffectedIds
            ->sum(function ($voucherId) use (
                $voucherMap
            ): float {
                $voucher = $voucherMap->get(
                    $voucherId
                );

                return $voucher
                    ? max(
                        (float) $voucher->total_debit,
                        (float) $voucher->total_credit
                    )
                    : 0;
            });

        return [
            'period' => [
                'from' => $from->format('d M Y'),
                'to' => $to->format('d M Y'),
            ],

            'stats' => [
                'vouchers_scanned' =>
                    $vouchers->count(),

                'passed_vouchers' =>
                    $passedVouchers,

                'affected_vouchers' =>
                    $affectedVoucherIds->count(),

                'total_issues' =>
                    $allIssues->count(),

                'filtered_issues' =>
                    $filteredIssues->count(),

                'critical' => $allIssues
                    ->where(
                        'severity_key',
                        'critical'
                    )
                    ->count(),

                'high' => $allIssues
                    ->where(
                        'severity_key',
                        'high'
                    )
                    ->count(),

                'medium' => $allIssues
                    ->where(
                        'severity_key',
                        'medium'
                    )
                    ->count(),

                'integrity_score' =>
                    $integrityScore,

                'affected_amount' => round(
                    (float) $affectedAmount,
                    2
                ),
            ],

            'issue_rows' =>
                $filteredIssues->all(),

            'generated_at' =>
                now()->format('d M Y h:i A'),
        ];
    }

    private function auditVoucherHeader(
        object $voucher,
        Collection $lines,
        Collection $issues,
    ): void {
        $headerDebit = round(
            (float) $voucher->total_debit,
            2
        );

        $headerCredit = round(
            (float) $voucher->total_credit,
            2
        );

        if (
            $headerDebit <= 0
            || ! $this->amountsEqual(
                $headerDebit,
                $headerCredit
            )
        ) {
            $this->addIssue(
                issues: $issues,
                voucher: $voucher,
                severity: 'critical',
                issueKey: 'header_unbalanced',
                issueLabel: 'Voucher Header Unbalanced',
                details:
                    'Header Debit PKR '
                    . number_format($headerDebit, 2)
                    . ' aur Credit PKR '
                    . number_format($headerCredit, 2)
                    . ' equal nahi hain.',
                recommendation:
                    'Voucher header aur source transaction ko review karke balanced correction voucher post karein.'
            );
        }

        if ($lines->isEmpty()) {
            $this->addIssue(
                issues: $issues,
                voucher: $voucher,
                severity: 'critical',
                issueKey: 'missing_lines',
                issueLabel: 'Voucher Lines Missing',
                details:
                    'Voucher ke against koi account voucher line available nahi hai.',
                recommendation:
                    'Source transaction aur posting service review karein. Direct database edit na karein.'
            );

            return;
        }

        $lineDebit = round(
            (float) $lines->sum('debit'),
            2
        );

        $lineCredit = round(
            (float) $lines->sum('credit'),
            2
        );

        if (! $this->amountsEqual(
            $lineDebit,
            $lineCredit
        )) {
            $this->addIssue(
                issues: $issues,
                voucher: $voucher,
                severity: 'critical',
                issueKey: 'lines_unbalanced',
                issueLabel: 'Voucher Lines Unbalanced',
                details:
                    'Lines Debit PKR '
                    . number_format($lineDebit, 2)
                    . ' aur Credit PKR '
                    . number_format($lineCredit, 2)
                    . ' equal nahi hain.',
                recommendation:
                    'Voucher lines ke account heads aur amounts review karein.'
            );
        }

        if (
            ! $this->amountsEqual(
                $headerDebit,
                $lineDebit
            )
            || ! $this->amountsEqual(
                $headerCredit,
                $lineCredit
            )
        ) {
            $this->addIssue(
                issues: $issues,
                voucher: $voucher,
                severity: 'high',
                issueKey: 'header_line_mismatch',
                issueLabel: 'Header and Lines Mismatch',
                details:
                    'Voucher header totals aur line totals match nahi kar rahe.',
                recommendation:
                    'Header totals ko voucher lines ke verified totals ke saath reconcile karein.'
            );
        }
    }

    private function auditVoucherLedgers(
        object $voucher,
        Collection $ledgers,
        Collection $issues,
    ): void {
        if ($ledgers->isEmpty()) {
            $this->addIssue(
                issues: $issues,
                voucher: $voucher,
                severity: 'critical',
                issueKey: 'missing_ledgers',
                issueLabel: 'GL Ledger Entries Missing',
                details:
                    'Voucher ke against General Ledger entries available nahi hain.',
                recommendation:
                    'Voucher ko repost na karein jab tak duplicate source posting check na ho.'
            );

            return;
        }

        $ledgerDebit = round(
            (float) $ledgers->sum('debit'),
            2
        );

        $ledgerCredit = round(
            (float) $ledgers->sum('credit'),
            2
        );

        if (! $this->amountsEqual(
            $ledgerDebit,
            $ledgerCredit
        )) {
            $this->addIssue(
                issues: $issues,
                voucher: $voucher,
                severity: 'critical',
                issueKey: 'ledgers_unbalanced',
                issueLabel: 'GL Entries Unbalanced',
                details:
                    'Ledger Debit PKR '
                    . number_format($ledgerDebit, 2)
                    . ' aur Credit PKR '
                    . number_format($ledgerCredit, 2)
                    . ' equal nahi hain.',
                recommendation:
                    'Ledger correction ke liye reversal aur corrected voucher workflow use karein.'
            );
        }

        if (
            ! $this->amountsEqual(
                (float) $voucher->total_debit,
                $ledgerDebit
            )
            || ! $this->amountsEqual(
                (float) $voucher->total_credit,
                $ledgerCredit
            )
        ) {
            $this->addIssue(
                issues: $issues,
                voucher: $voucher,
                severity: 'high',
                issueKey: 'header_ledger_mismatch',
                issueLabel: 'Header and GL Mismatch',
                details:
                    'Voucher header totals aur GL ledger totals match nahi karte.',
                recommendation:
                    'Voucher lines aur GL entries dono ko source transaction ke saath verify karein.'
            );
        }
    }

    private function auditLineLedgerLinks(
        object $voucher,
        Collection $lines,
        Collection $ledgers,
        Collection $issues,
    ): void {
        if (
            $lines->isEmpty()
            || $ledgers->isEmpty()
        ) {
            return;
        }

        $ledgersByLine = $ledgers->groupBy(
            'account_voucher_line_id'
        );

        $lineIds = $lines
            ->pluck('id')
            ->map(fn ($id): int => (int) $id);

        foreach ($lines as $line) {
            $linkedLedgers = $ledgersByLine->get(
                $line->id,
                collect()
            );

            if ($linkedLedgers->isEmpty()) {
                $this->addIssue(
                    issues: $issues,
                    voucher: $voucher,
                    severity: 'high',
                    issueKey: 'line_ledger_missing',
                    issueLabel: 'Line GL Entry Missing',
                    details:
                        'Voucher Line #'
                        . $line->id
                        . ' ke against GL entry nahi hai.',
                    recommendation:
                        'Posting transaction inspect karein aur duplicate voucher check karne ke baad correction karein.'
                );

                continue;
            }

            if ($linkedLedgers->count() > 1) {
                $this->addIssue(
                    issues: $issues,
                    voucher: $voucher,
                    severity: 'critical',
                    issueKey: 'duplicate_line_ledger',
                    issueLabel: 'Duplicate GL Entry',
                    details:
                        'Voucher Line #'
                        . $line->id
                        . ' ke against '
                        . $linkedLedgers->count()
                        . ' ledger entries hain.',
                    recommendation:
                        'Duplicate posting source identify karke controlled reversal karein.'
                );

                continue;
            }

            $ledger = $linkedLedgers->first();

            $accountMismatch =
                (int) $line->account_head_id
                !== (int) $ledger->account_head_id;

            $debitMismatch = ! $this->amountsEqual(
                (float) $line->debit,
                (float) $ledger->debit
            );

            $creditMismatch = ! $this->amountsEqual(
                (float) $line->credit,
                (float) $ledger->credit
            );

            if (
                $accountMismatch
                || $debitMismatch
                || $creditMismatch
            ) {
                $this->addIssue(
                    issues: $issues,
                    voucher: $voucher,
                    severity: 'high',
                    issueKey: 'line_ledger_mismatch',
                    issueLabel: 'Line and GL Entry Mismatch',
                    details:
                        'Voucher Line #'
                        . $line->id
                        . ' ka Account Head ya amount GL entry se match nahi karta.',
                    recommendation:
                        'Voucher line aur linked GL entry ko source document ke mutabiq reconcile karein.'
                );
            }
        }

        foreach ($ledgers as $ledger) {
            $lineId = $ledger
                ->account_voucher_line_id;

            if (
                blank($lineId)
                || ! $lineIds->contains(
                    (int) $lineId
                )
            ) {
                $this->addIssue(
                    issues: $issues,
                    voucher: $voucher,
                    severity: 'medium',
                    issueKey: 'orphan_ledger_link',
                    issueLabel: 'Orphan GL Link',
                    details:
                        'Ledger Entry #'
                        . $ledger->id
                        . ' valid voucher line se linked nahi hai.',
                    recommendation:
                        'Ledger entry ka originating voucher line aur posting service inspect karein.'
                );
            }
        }
    }

    private function auditLedgerStatuses(
        object $voucher,
        Collection $ledgers,
        Collection $issues,
    ): void {
        if ($ledgers->isEmpty()) {
            return;
        }

        $expectedStatus = $voucher->status
            === 'Cancelled'
                ? 'Cancelled'
                : 'Posted';

        $mismatched = $ledgers->filter(
            fn (object $ledger): bool =>
                $ledger->status
                !== $expectedStatus
        );

        if ($mismatched->isNotEmpty()) {
            $this->addIssue(
                issues: $issues,
                voucher: $voucher,
                severity: 'medium',
                issueKey: 'ledger_status_mismatch',
                issueLabel: 'Voucher and GL Status Mismatch',
                details:
                    $mismatched->count()
                    . ' ledger entries ka status voucher status ke mutabiq nahi hai.',
                recommendation:
                    'Voucher aur tamam linked GL entries ka posting/cancellation status synchronize karein.'
            );
        }
    }

    private function auditSourceLink(
        object $voucher,
        Collection $issues,
    ): void {
        if ($this->hasValidLinkedReversal($voucher)) {
            return;
        }
        if (
            blank($voucher->source_model)
            || blank($voucher->source_id)
        ) {
            return;
        }

        $modelClass = (string)
            $voucher->source_model;

        if (
            ! class_exists($modelClass)
            || ! is_subclass_of(
                $modelClass,
                Model::class
            )
        ) {
            return;
        }

        try {
            /** @var Model $model */
            $model = new $modelClass();

            $table = $model->getTable();

            if (! Schema::hasTable($table)) {
                return;
            }

            $record = $modelClass::query()
                ->find($voucher->source_id);

            if (! $record) {
                $this->addIssue(
                    issues: $issues,
                    voucher: $voucher,
                    severity: 'medium',
                    issueKey: 'source_record_missing',
                    issueLabel: 'Source Record Missing',
                    details:
                        'Voucher source record '
                        . class_basename($modelClass)
                        . ' #'
                        . $voucher->source_id
                        . ' available nahi hai.',
                    recommendation:
                        'Source record deletion logs aur voucher posting history review karein.'
                );

                return;
            }

            if (
                Schema::hasColumn(
                    $table,
                    'account_voucher_id'
                )
                && (int) (
                    $record->account_voucher_id
                    ?? 0
                ) !== (int) $voucher->id
            ) {
                $this->addIssue(
                    issues: $issues,
                    voucher: $voucher,
                    severity: 'high',
                    issueKey: 'source_voucher_mismatch',
                    issueLabel: 'Source Voucher Link Mismatch',
                    details:
                        'Source record ka account_voucher_id is voucher se linked nahi hai.',
                    recommendation:
                        'Duplicate voucher check karke source record ka verified voucher linkage correct karein.'
                );
            }
        } catch (Throwable) {
            // Audit ko source model failure ki wajah se stop nahi karna.
        }
    }


    private function hasValidLinkedReversal(
        object $voucher,
    ): bool {
        if (
            ($voucher->status ?? null) !== 'Posted'
            || ! \Illuminate\Support\Facades\Schema::hasColumn(
                'account_vouchers',
                'reversal_of_voucher_id',
            )
        ) {
            return false;
        }

        $reversal =
            \Illuminate\Support\Facades\DB::table(
                'account_vouchers',
            )
                ->where(
                    'reversal_of_voucher_id',
                    (int) $voucher->id,
                )
                ->where('status', 'Posted')
                ->first();

        if (! $reversal) {
            return false;
        }

        $headerDebit =
            round(
                (float) ($voucher->total_debit ?? 0),
                2,
            );

        $headerCredit =
            round(
                (float) ($voucher->total_credit ?? 0),
                2,
            );

        $reversalDebit =
            round(
                (float) ($reversal->total_debit ?? 0),
                2,
            );

        $reversalCredit =
            round(
                (float) ($reversal->total_credit ?? 0),
                2,
            );

        if (
            $headerDebit <= 0
            || ! $this->amountsEqual(
                $headerDebit,
                $headerCredit,
            )
            || ! $this->amountsEqual(
                $reversalDebit,
                $reversalCredit,
            )
            || ! $this->amountsEqual(
                $headerDebit,
                $reversalDebit,
            )
        ) {
            return false;
        }

        $originalLines =
            \Illuminate\Support\Facades\DB::table(
                'account_voucher_lines',
            )
                ->where(
                    'account_voucher_id',
                    (int) $voucher->id,
                )
                ->get([
                    'account_head_id',
                    'debit',
                    'credit',
                ]);

        $reversalLines =
            \Illuminate\Support\Facades\DB::table(
                'account_voucher_lines',
            )
                ->where(
                    'account_voucher_id',
                    (int) $reversal->id,
                )
                ->get([
                    'id',
                    'account_head_id',
                    'debit',
                    'credit',
                ]);

        if (
            $originalLines->count() < 2
            || $originalLines->count()
                !== $reversalLines->count()
        ) {
            return false;
        }

        $expected =
            $originalLines
                ->map(
                    static fn (object $line): string =>
                        sprintf(
                            '%020d|%020.2f|%020.2f',
                            (int) $line->account_head_id,
                            round((float) $line->credit, 2),
                            round((float) $line->debit, 2),
                        ),
                )
                ->sort()
                ->values()
                ->all();

        $actual =
            $reversalLines
                ->map(
                    static fn (object $line): string =>
                        sprintf(
                            '%020d|%020.2f|%020.2f',
                            (int) $line->account_head_id,
                            round((float) $line->debit, 2),
                            round((float) $line->credit, 2),
                        ),
                )
                ->sort()
                ->values()
                ->all();

        if ($expected !== $actual) {
            return false;
        }

        $ledgerRows =
            \Illuminate\Support\Facades\DB::table(
                'account_ledgers',
            )
                ->where(
                    'account_voucher_id',
                    (int) $reversal->id,
                )
                ->get([
                    'account_voucher_line_id',
                    'account_head_id',
                    'debit',
                    'credit',
                    'status',
                ]);

        if (
            $ledgerRows->count()
                !== $reversalLines->count()
            || $ledgerRows->contains(
                static fn (object $ledger): bool =>
                    $ledger->status !== 'Posted',
            )
        ) {
            return false;
        }

        $lineMap =
            $reversalLines->keyBy('id');

        foreach ($ledgerRows as $ledger) {
            $line =
                $lineMap->get(
                    $ledger->account_voucher_line_id,
                );

            if (
                ! $line
                || (int) $ledger->account_head_id
                    !== (int) $line->account_head_id
                || ! $this->amountsEqual(
                    (float) $ledger->debit,
                    (float) $line->debit,
                )
                || ! $this->amountsEqual(
                    (float) $ledger->credit,
                    (float) $line->credit,
                )
            ) {
                return false;
            }
        }

        return true;
    }

    private function auditDuplicateSourcePostings(
        Collection $vouchers,
        Collection $issues,
    ): void {
        $groups = $vouchers
            ->filter(
                fn (object $voucher): bool =>
                    $voucher->status === 'Posted'
                    && filled($voucher->source_model)
                    && filled($voucher->source_id)
            )
            ->groupBy(
                fn (object $voucher): string =>
                    $voucher->source_model
                    . '|'
                    . $voucher->source_id
            )
            ->filter(
                fn (Collection $group): bool =>
                    $group->count() > 1
            );

        foreach ($groups as $group) {
            $voucherNumbers = $group
                ->pluck('voucher_no')
                ->filter()
                ->implode(', ');

            foreach ($group as $voucher) {
                $this->addIssue(
                    issues: $issues,
                    voucher: $voucher,
                    severity: 'critical',
                    issueKey: 'duplicate_source_posting',
                    issueLabel: 'Duplicate Source Posting',
                    details:
                        'Same source transaction ke multiple Posted vouchers hain: '
                        . $voucherNumbers,
                    recommendation:
                        'Source transaction aur vouchers verify karke duplicate voucher ko controlled reversal se cancel karein.'
                );
            }
        }
    }

    private function addIssue(
        Collection $issues,
        object $voucher,
        string $severity,
        string $issueKey,
        string $issueLabel,
        string $details,
        string $recommendation,
    ): void {
        $severityLabel = match ($severity) {
            'critical' => 'Critical',
            'high' => 'High',
            'medium' => 'Medium',
            default => 'Info',
        };

        $source = filled($voucher->source_type)
            ? $voucher->source_type
            : (
                filled($voucher->source_model)
                    ? class_basename(
                        $voucher->source_model
                    )
                    : 'Manual Voucher'
            );

        $sourceNo = $voucher->source_no
            ?: $voucher->reference_no
            ?: '-';

        $searchText = implode(' ', [
            $voucher->voucher_no,
            $voucher->party_name,
            $source,
            $sourceNo,
            $issueLabel,
            $details,
            $voucher->status,
        ]);

        $issues->push([
            'voucher_id' => (int) $voucher->id,
            'voucher_no' => $voucher->voucher_no,
            'voucher_date' => Carbon::parse(
                $voucher->voucher_date
            )->format('d M Y'),

            'voucher_date_value' => Carbon::parse(
                $voucher->voucher_date
            )->format('Y-m-d'),

            'voucher_type' =>
                $voucher->voucher_type,

            'voucher_status' =>
                $voucher->status,

            'party_name' =>
                $voucher->party_name ?: '-',

            'source' => $source,
            'source_no' => $sourceNo,

            'amount' => round(
                max(
                    (float) $voucher->total_debit,
                    (float) $voucher->total_credit
                ),
                2
            ),

            'severity_key' => $severity,
            'severity' => $severityLabel,

            'issue_key' => $issueKey,
            'issue_label' => $issueLabel,

            'details' => $details,

            'recommendation' =>
                $recommendation,

            'voucher_url' =>
                $this->voucherUrl(
                    (int) $voucher->id
                ),

            'search_text' => $searchText,
        ]);
    }

    private function voucherUrl(
        int $voucherId
    ): string {
        $routeBase =
            'filament.admin.resources.account-vouchers';

        foreach (['view', 'edit'] as $page) {
            $routeName = $routeBase
                . '.'
                . $page;

            if (! Route::has($routeName)) {
                continue;
            }

            try {
                return route(
                    $routeName,
                    ['record' => $voucherId]
                );
            } catch (Throwable) {
                // Fallback below.
            }
        }

        return url(
            '/admin/account-vouchers/'
            . $voucherId
        );
    }

    private function requiredTablesExist(): bool
    {
        return Schema::hasTable('account_vouchers')
            && Schema::hasTable(
                'account_voucher_lines'
            )
            && Schema::hasTable(
                'account_ledgers'
            );
    }

    private function amountsEqual(
        float $first,
        float $second,
    ): bool {
        return abs(
            round($first, 2)
            - round($second, 2)
        ) < self::TOLERANCE;
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
            ],

            'stats' => [
                'vouchers_scanned' => 0,
                'passed_vouchers' => 0,
                'affected_vouchers' => 0,
                'total_issues' => 0,
                'filtered_issues' => 0,
                'critical' => 0,
                'high' => 0,
                'medium' => 0,
                'integrity_score' => 100,
                'affected_amount' => 0,
            ],

            'issue_rows' => [],

            'generated_at' =>
                now()->format('d M Y h:i A'),
        ];
    }
}