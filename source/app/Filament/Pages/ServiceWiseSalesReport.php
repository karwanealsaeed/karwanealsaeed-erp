<?php

namespace App\Filament\Pages;

use App\Models\FlightBooking;
use App\Models\HotelBooking;
use App\Models\UmrahBooking;
use App\Models\VisaApplication;
use Carbon\Carbon;
use Filament\Pages\Page;

class ServiceWiseSalesReport extends Page
{

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chart-pie';


    protected static string | \UnitEnum | null $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 10;
    protected static ?string $navigationLabel = 'Service Wise Sales Report';
    protected static ?string $title = 'Service Wise Sales Report';

    protected string $view = 'filament.pages.service-wise-sales-report';

    public ?string $fromDate = null;

    public ?string $toDate = null;

    public function mount(): void
    {
        $this->fromDate = now()->startOfMonth()->format('Y-m-d');
        $this->toDate = now()->format('Y-m-d');
    }

    public function getReportData(): array
    {
        $fromDate = Carbon::parse(
            $this->fromDate ?? now()->startOfMonth()
        )->startOfDay();

        $toDate = Carbon::parse(
            $this->toDate ?? now()
        )->endOfDay();

        if ($fromDate->greaterThan($toDate)) {
            [$fromDate, $toDate] = [
                $toDate->copy()->startOfDay(),
                $fromDate->copy()->endOfDay(),
            ];
        }

        $visaQuery = VisaApplication::query()
            ->whereBetween('created_at', [$fromDate, $toDate]);

        $flightQuery = FlightBooking::query()
            ->whereBetween('created_at', [$fromDate, $toDate]);

        $umrahQuery = UmrahBooking::query()
            ->whereBetween('created_at', [$fromDate, $toDate]);

        $hotelQuery = HotelBooking::query()
            ->whereBetween('created_at', [$fromDate, $toDate]);

        $visa = [
            'service' => 'Visa',
            'count' => (clone $visaQuery)->count(),
            'total' => (float) (clone $visaQuery)->sum('total_amount'),
            'paid' => (float) (clone $visaQuery)->sum('paid_amount'),
            'balance' => (float) (clone $visaQuery)->sum('balance_amount'),
        ];

        $flight = [
            'service' => 'Flight',
            'count' => (clone $flightQuery)->count(),
            'total' => (float) (clone $flightQuery)->sum('total_amount'),
            'paid' => (float) (clone $flightQuery)->sum('paid_amount'),
            'balance' => (float) (clone $flightQuery)->sum('balance_amount'),
        ];

        $umrah = [
            'service' => 'Umrah',
            'count' => (clone $umrahQuery)->count(),
            'total' => (float) (clone $umrahQuery)->sum('package_amount'),
            'paid' => (float) (clone $umrahQuery)->sum('paid_amount'),
            'balance' => (float) (clone $umrahQuery)->sum('balance_amount'),
        ];

        $hotel = [
            'service' => 'Hotel',
            'count' => (clone $hotelQuery)->count(),
            'total' => (float) (clone $hotelQuery)->sum('total_amount'),
            'paid' => (float) (clone $hotelQuery)->sum('paid_amount'),
            'balance' => (float) (clone $hotelQuery)->sum('balance_amount'),
        ];

        $rows = [
            $visa,
            $flight,
            $umrah,
            $hotel,
        ];

        return [
            'from_date' => $fromDate->format('Y-m-d'),
            'to_date' => $toDate->format('Y-m-d'),
            'rows' => $rows,
            'total_records' => collect($rows)->sum('count'),
            'total_sales' => collect($rows)->sum('total'),
            'total_paid' => collect($rows)->sum('paid'),
            'total_balance' => collect($rows)->sum('balance'),
        ];
    }
    public static function canAccess(): bool
    {
        return auth()->user()?->canViewReports() ?? false;
    }
}


