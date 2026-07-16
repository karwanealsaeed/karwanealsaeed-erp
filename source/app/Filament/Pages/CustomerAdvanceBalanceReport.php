<?php

namespace App\Filament\Pages;

use App\Services\CustomerAdvanceBalanceReportService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use UnitEnum;

class CustomerAdvanceBalanceReport extends Page
{
    protected static string|BackedEnum|null
        $navigationIcon =
            'heroicon-o-banknotes';

    protected static string|UnitEnum|null
        $navigationGroup = 'Reports';

    protected static ?string $navigationLabel =
        'Customer Advance Balance';

    protected static ?string $title =
        'Customer Advance Balance Report';

    protected static ?string $slug =
        'customer-advance-balance-report';

    protected static ?int $navigationSort = 23;

    protected string $view =
        'filament.pages.customer-advance-balance-report';

    public string $asOfDate = '';

    public string $customerType = 'all';

    public string $customerStatus = 'Active';

    public string $balanceFilter = 'positive';

    public string $search = '';

    public function mount(): void
    {
        $this->asOfDate =
            today()->format('Y-m-d');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasPermission(
            'reports.view'
        ) ?? false;
    }

    public function resetFilters(): void
    {
        $this->asOfDate =
            today()->format('Y-m-d');

        $this->customerType = 'all';
        $this->customerStatus = 'Active';
        $this->balanceFilter = 'positive';
        $this->search = '';
    }

    public function customerTypeOptions(): array
    {
        return [
            'all' => 'All Customer Types',
            'Customer' => 'Customers',
            'Agent' => 'Agents',
        ];
    }

    public function customerStatusOptions(): array
    {
        return [
            'all' => 'All Statuses',
            'Active' => 'Active',
            'Inactive' => 'Inactive',
        ];
    }

    public function balanceFilterOptions(): array
    {
        return [
            'positive' =>
                'Advance Available',

            'zero' =>
                'Fully Refunded',

            'negative' =>
                'Over Refunded',

            'all' =>
                'All Advance Records',
        ];
    }

    public function reportData(): array
    {
        return app(
            CustomerAdvanceBalanceReportService::class
        )->generate(
            asOfDate: $this->asOfDate,
            customerType:
                $this->customerType,

            customerStatus:
                $this->customerStatus,

            balanceFilter:
                $this->balanceFilter,

            search: $this->search,
        );
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }
}