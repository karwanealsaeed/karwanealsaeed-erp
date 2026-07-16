<?php

namespace App\Filament\Pages;

use App\Models\Country;
use App\Models\VisaApplication;
use Filament\Pages\Page;

class CountryWiseVisaReport extends Page
{

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-globe-alt';


    protected static string | \UnitEnum | null $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 20;
    protected static ?string $navigationLabel = 'Country Wise Visa Report';
    protected static ?string $title = 'Country Wise Visa Report';

    protected string $view = 'filament.pages.country-wise-visa-report';

    public function getReportData(): array
    {
        $rows = [];

        $countries = Country::orderBy('name')->get();

        foreach ($countries as $country) {
            $query = VisaApplication::where('country_id', $country->id);

            $totalApplications = (clone $query)->count();

            if ($totalApplications <= 0) {
                continue;
            }

            $totalSales = (clone $query)->sum('total_amount');
            $paidAmount = (clone $query)->sum('paid_amount');
            $balanceAmount = (clone $query)->sum('balance_amount');

            $approved = (clone $query)->where('status', 'Approved')->count();
            $rejected = (clone $query)->where('status', 'Rejected')->count();
            $processing = (clone $query)->where('status', 'Processing')->count();
            $applied = (clone $query)->where('status', 'Applied')->count();
            $delivered = (clone $query)->where('status', 'Delivered')->count();

            $rows[] = [
                'country_name' => $country->name,
                'country_code' => $country->code,
                'total_applications' => $totalApplications,
                'total_sales' => (float) $totalSales,
                'paid_amount' => (float) $paidAmount,
                'balance_amount' => (float) $balanceAmount,
                'approved' => $approved,
                'rejected' => $rejected,
                'processing' => $processing,
                'applied' => $applied,
                'delivered' => $delivered,
            ];
        }

        return [
            'rows' => $rows,
            'total_countries' => count($rows),
            'total_applications' => collect($rows)->sum('total_applications'),
            'total_sales' => collect($rows)->sum('total_sales'),
            'total_paid' => collect($rows)->sum('paid_amount'),
            'total_balance' => collect($rows)->sum('balance_amount'),
            'total_approved' => collect($rows)->sum('approved'),
            'total_rejected' => collect($rows)->sum('rejected'),
            'total_processing' => collect($rows)->sum('processing'),
            'total_applied' => collect($rows)->sum('applied'),
            'total_delivered' => collect($rows)->sum('delivered'),
        ];
    }
    public static function canAccess(): bool
    {
        return auth()->user()?->canViewReports() ?? false;
    }
}


