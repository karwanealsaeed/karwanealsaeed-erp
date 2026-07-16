<?php

namespace App\Filament\Pages;

use App\Services\AccountingPostingAuditService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use UnitEnum;

class AccountingPostingAudit extends Page
{
    protected static string|BackedEnum|null
        $navigationIcon =
            'heroicon-o-shield-check';

    protected static string|UnitEnum|null
        $navigationGroup = 'Finance';

    protected static ?string $navigationLabel =
        'GL Posting Audit';

    protected static ?string $title =
        'Unified GL Posting Audit';

    protected static ?string $slug =
        'accounting-posting-audit';

    protected static ?int $navigationSort = 44;

    protected string $view =
        'filament.pages.accounting-posting-audit';

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $voucherStatus = 'Posted';

    public string $severity = 'all';

    public string $issueType = 'all';

    public string $search = '';

    public function mount(): void
    {
        $this->dateFrom = now()
            ->startOfMonth()
            ->format('Y-m-d');

        $this->dateTo = now()
            ->endOfMonth()
            ->format('Y-m-d');
    }

    public function resetFilters(): void
    {
        $this->dateFrom = now()
            ->startOfMonth()
            ->format('Y-m-d');

        $this->dateTo = now()
            ->endOfMonth()
            ->format('Y-m-d');

        $this->voucherStatus = 'Posted';
        $this->severity = 'all';
        $this->issueType = 'all';
        $this->search = '';
    }

    public function voucherStatusOptions(): array
    {
        return [
            'Posted' => 'Posted Vouchers',
            'Cancelled' => 'Cancelled Vouchers',
            'all' => 'All Voucher Statuses',
        ];
    }

    public function severityOptions(): array
    {
        return [
            'all' => 'All Severities',
            'critical' => 'Critical',
            'high' => 'High',
            'medium' => 'Medium',
            'info' => 'Info',
        ];
    }

    public function issueTypeOptions(): array
    {
        return [
            'all' => 'All Issue Types',

            'header_unbalanced' =>
                'Voucher Header Unbalanced',

            'missing_lines' =>
                'Voucher Lines Missing',

            'lines_unbalanced' =>
                'Voucher Lines Unbalanced',

            'header_line_mismatch' =>
                'Header and Lines Mismatch',

            'missing_ledgers' =>
                'GL Entries Missing',

            'ledgers_unbalanced' =>
                'GL Entries Unbalanced',

            'header_ledger_mismatch' =>
                'Header and GL Mismatch',

            'line_ledger_missing' =>
                'Line GL Entry Missing',

            'duplicate_line_ledger' =>
                'Duplicate GL Entry',

            'line_ledger_mismatch' =>
                'Line and GL Mismatch',

            'orphan_ledger_link' =>
                'Orphan GL Link',

            'ledger_status_mismatch' =>
                'GL Status Mismatch',

            'duplicate_source_posting' =>
                'Duplicate Source Posting',

            'source_record_missing' =>
                'Source Record Missing',

            'source_voucher_mismatch' =>
                'Source Voucher Link Mismatch',
        ];
    }

    public function reportData(): array
    {
        return app(
            AccountingPostingAuditService::class
        )->generate(
            dateFrom: $this->dateFrom,
            dateTo: $this->dateTo,
            voucherStatus: $this->voucherStatus,
            severity: $this->severity,
            issueType: $this->issueType,
            search: $this->search,
        );
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }
}