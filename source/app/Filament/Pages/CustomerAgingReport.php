<?php

namespace App\Filament\Pages;

use App\Services\CustomerAgingReportService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use UnitEnum;

class CustomerAgingReport extends Page
{

    use \App\Filament\Concerns\HasRbacPageAuthorization;
protected static string|BackedEnum|null $navigationIcon =
        'heroicon-o-clock';

    protected static string|UnitEnum|null $navigationGroup =
        'Reports';

    protected static ?string $navigationLabel =
        'Customer Aging';

    protected static ?string $title =
        'Customer Outstanding & Aging';

    protected static ?string $slug =
        'customer-aging-report';

    protected static ?int $navigationSort = 21;

    protected string $view =
        'filament.pages.customer-aging-report';

    public int|string|null $customerId = null;

    public ?string $customerType = null;

    public ?string $serviceType = null;

    public function resetFilters(): void
    {
        $this->customerId = null;
        $this->customerType = null;
        $this->serviceType = null;
    }

    public function customerOptions(): array
    {
        if (! Schema::hasTable('customers')) {
            return [];
        }

        return DB::table('customers')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'mobile',
                'customer_type',
            ])
            ->mapWithKeys(function (object $customer): array {
                $label = $customer->name;

                if (filled($customer->mobile)) {
                    $label .= ' - ' . $customer->mobile;
                }

                if (filled($customer->customer_type)) {
                    $label .= ' (' . $customer->customer_type . ')';
                }

                return [
                    $customer->id => $label,
                ];
            })
            ->toArray();
    }

    public function customerTypeOptions(): array
    {
        return [
            'Customer' => 'Customer — B2C',
            'Agent' => 'Agent — B2B',
        ];
    }

    public function serviceTypeOptions(): array
    {
        return [
            'Visa' => 'Visa',
            'Flight' => 'Flight',
            'Umrah' => 'Umrah',
            'Hotel' => 'Hotel',
            'Other' => 'Other',
        ];
    }

    public function reportData(): array
    {
        return app(
            CustomerAgingReportService::class
        )->generate(
            customerId: $this->customerId,
            customerType: $this->customerType,
            serviceType: $this->serviceType,
        );
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }
}