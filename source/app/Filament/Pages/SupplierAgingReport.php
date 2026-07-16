<?php

namespace App\Filament\Pages;

use App\Services\SupplierAgingReportService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use UnitEnum;

class SupplierAgingReport extends Page
{
    protected static string|BackedEnum|null
        $navigationIcon = 'heroicon-o-building-storefront';

    protected static string|UnitEnum|null
        $navigationGroup = 'Reports';

    protected static ?string $navigationLabel =
        'Supplier Aging';

    protected static ?string $title =
        'Supplier Outstanding & Aging';

    protected static ?string $slug =
        'supplier-aging-report';

    protected static ?int $navigationSort = 22;

    protected string $view =
        'filament.pages.supplier-aging-report';

    public int|string|null $supplierId = null;

    public ?string $supplierType = null;

    public ?string $serviceType = null;

    public function resetFilters(): void
    {
        $this->supplierId = null;
        $this->supplierType = null;
        $this->serviceType = null;
    }

    public function supplierOptions(): array
    {
        if (! Schema::hasTable('suppliers')) {
            return [];
        }

        return DB::table('suppliers')
            ->orderBy('company_name')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'company_name',
                'mobile',
                'supplier_type',
            ])
            ->mapWithKeys(function (
                object $supplier
            ): array {
                $label = filled(
                    $supplier->company_name
                )
                    ? $supplier->company_name
                        . ' - '
                        . $supplier->name
                    : $supplier->name;

                if (filled($supplier->mobile)) {
                    $label .= ' - '
                        . $supplier->mobile;
                }

                if (filled($supplier->supplier_type)) {
                    $label .= ' ('
                        . $supplier->supplier_type
                        . ')';
                }

                return [
                    $supplier->id => $label,
                ];
            })
            ->toArray();
    }

    public function supplierTypeOptions(): array
    {
        return [
            'Airline' => 'Airline',
            'Hotel' => 'Hotel',
            'Visa' => 'Visa',
            'Transport' => 'Transport',
            'Other' => 'Other',
        ];
    }

    public function serviceTypeOptions(): array
    {
        return [
            'Airline' => 'Airline / Ticketing',
            'Hotel' => 'Hotel',
            'Visa' => 'Visa',
            'Transport' => 'Transport',
            'Umrah' => 'Umrah',
            'Insurance' => 'Insurance',
            'Tour' => 'Tour',
            'Other' => 'Other',
        ];
    }

    public function reportData(): array
    {
        return app(
            SupplierAgingReportService::class
        )->generate(
            supplierId: $this->supplierId,
            supplierType: $this->supplierType,
            serviceType: $this->serviceType,
        );
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }
}