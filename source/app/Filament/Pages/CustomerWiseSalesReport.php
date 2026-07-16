<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Models\FlightBooking;
use App\Models\HotelBooking;
use App\Models\UmrahBooking;
use App\Models\VisaApplication;
use Filament\Pages\Page;

class CustomerWiseSalesReport extends Page
{

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user';


    protected static string | \UnitEnum | null $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 11;
    protected static ?string $navigationLabel = 'Customer Wise Sales Report';
    protected static ?string $title = 'Customer Wise Sales Report';

    protected string $view = 'filament.pages.customer-wise-sales-report';

    public function getReportData(): array
    {
        $rows = [];

        $customers = Customer::orderBy('name')->get();

        foreach ($customers as $customer) {
            $visaSales = VisaApplication::where('customer_id', $customer->id)->sum('total_amount');
            $visaPaid = VisaApplication::where('customer_id', $customer->id)->sum('paid_amount');
            $visaBalance = VisaApplication::where('customer_id', $customer->id)->sum('balance_amount');

            $flightSales = FlightBooking::where('customer_id', $customer->id)->sum('total_amount');
            $flightPaid = FlightBooking::where('customer_id', $customer->id)->sum('paid_amount');
            $flightBalance = FlightBooking::where('customer_id', $customer->id)->sum('balance_amount');

            $umrahSales = UmrahBooking::where('customer_id', $customer->id)->sum('package_amount');
            $umrahPaid = UmrahBooking::where('customer_id', $customer->id)->sum('paid_amount');
            $umrahBalance = UmrahBooking::where('customer_id', $customer->id)->sum('balance_amount');

            $hotelSales = HotelBooking::where('customer_id', $customer->id)->sum('total_amount');
            $hotelPaid = HotelBooking::where('customer_id', $customer->id)->sum('paid_amount');
            $hotelBalance = HotelBooking::where('customer_id', $customer->id)->sum('balance_amount');

            $totalSales = $visaSales + $flightSales + $umrahSales + $hotelSales;
            $totalPaid = $visaPaid + $flightPaid + $umrahPaid + $hotelPaid;
            $totalBalance = $visaBalance + $flightBalance + $umrahBalance + $hotelBalance;

            if ($totalSales <= 0) {
                continue;
            }

            $rows[] = [
                'customer_name' => $customer->name,
                'mobile' => $customer->mobile,
                'passport_no' => $customer->passport_no,

                'visa_sales' => (float) $visaSales,
                'flight_sales' => (float) $flightSales,
                'umrah_sales' => (float) $umrahSales,
                'hotel_sales' => (float) $hotelSales,

                'total_sales' => (float) $totalSales,
                'total_paid' => (float) $totalPaid,
                'total_balance' => (float) $totalBalance,
            ];
        }

        return [
            'rows' => $rows,

            'visa_total' => collect($rows)->sum('visa_sales'),
            'flight_total' => collect($rows)->sum('flight_sales'),
            'umrah_total' => collect($rows)->sum('umrah_sales'),
            'hotel_total' => collect($rows)->sum('hotel_sales'),

            'grand_sales' => collect($rows)->sum('total_sales'),
            'grand_paid' => collect($rows)->sum('total_paid'),
            'grand_balance' => collect($rows)->sum('total_balance'),
            'total_customers' => count($rows),
        ];
    }
    public static function canAccess(): bool
    {
        return auth()->user()?->canViewReports() ?? false;
    }
}


