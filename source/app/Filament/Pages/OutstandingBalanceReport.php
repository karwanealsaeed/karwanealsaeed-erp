<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Models\FlightBooking;
use App\Models\HotelBooking;
use App\Models\UmrahBooking;
use App\Models\VisaApplication;
use Filament\Pages\Page;

class OutstandingBalanceReport extends Page
{

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-scale';


    protected static string | \UnitEnum | null $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 15;
    protected static ?string $navigationLabel = 'Outstanding Balance Report';
    protected static ?string $title = 'Outstanding Balance Report';

    protected string $view = 'filament.pages.outstanding-balance-report';

    public function getReportData(): array
    {
        $rows = [];

        $customers = Customer::orderBy('name')->get();

        foreach ($customers as $customer) {
            $visaBalance = VisaApplication::where('customer_id', $customer->id)->sum('balance_amount');
            $flightBalance = FlightBooking::where('customer_id', $customer->id)->sum('balance_amount');
            $umrahBalance = UmrahBooking::where('customer_id', $customer->id)->sum('balance_amount');
            $hotelBalance = HotelBooking::where('customer_id', $customer->id)->sum('balance_amount');

            $totalBalance = $visaBalance + $flightBalance + $umrahBalance + $hotelBalance;

            if ($totalBalance <= 0) {
                continue;
            }

            $rows[] = [
                'customer_name' => $customer->name,
                'mobile' => $customer->mobile,
                'passport_no' => $customer->passport_no,
                'visa_balance' => $visaBalance,
                'flight_balance' => $flightBalance,
                'umrah_balance' => $umrahBalance,
                'hotel_balance' => $hotelBalance,
                'total_balance' => $totalBalance,
            ];
        }

        return [
            'rows' => $rows,
            'visa_total' => collect($rows)->sum('visa_balance'),
            'flight_total' => collect($rows)->sum('flight_balance'),
            'umrah_total' => collect($rows)->sum('umrah_balance'),
            'hotel_total' => collect($rows)->sum('hotel_balance'),
            'grand_total' => collect($rows)->sum('total_balance'),
        ];
    }
    public static function canAccess(): bool
    {
        return auth()->user()?->canViewReports() ?? false;
    }
}


