<?php

namespace App\Filament\Pages;

use App\Services\AccountingApprovalCenterService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use UnitEnum;

class AccountingApprovalCenter extends Page
{
    protected static string|BackedEnum|null
        $navigationIcon =
            'heroicon-o-check-badge';

    protected static string|UnitEnum|null
        $navigationGroup = 'Finance';

    protected static ?string $navigationLabel =
        'Accounting Approvals';

    protected static ?string $title =
        'Central Accounting Approval Center';

    protected static ?string $slug =
        'accounting-approval-center';

    protected static ?int $navigationSort = 43;

    protected string $view =
        'filament.pages.accounting-approval-center';

    public string $queueType = 'all';

    public string $search = '';

    public function resetFilters(): void
    {
        $this->queueType = 'all';
        $this->search = '';
    }

    public function queueOptions(): array
    {
        return [
            'all' => 'All Pending Approvals',

            'supplier_bills' =>
                'Supplier Bills',

            'supplier_payments' =>
                'Supplier Payments',

            'customer_payments' =>
                'Customer Payment Uploads',

            'staff_ledger' =>
                'Staff Ledger Entries',

            'refunds' =>
                'Customer Refunds',
        ];
    }

    public function reportData(): array
    {
        return app(
            AccountingApprovalCenterService::class
        )->generate(
            queueType: $this->queueType,
            search: $this->search,
        );
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }
}