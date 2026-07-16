<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\FlightBooking;
use App\Models\FlightPayment;
use App\Models\HotelBooking;
use App\Models\HotelPayment;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\UmrahBooking;
use App\Models\UmrahPayment;
use App\Models\VisaApplication;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStats extends StatsOverviewWidget
{

    public static function canView(): bool
    {
        return auth()->user()?->hasPermission('reports.view') ?? false;
    }
    protected function getStats(): array
    {
        $visaSales = (float) VisaApplication::sum('total_amount');
        $flightSales = (float) FlightBooking::sum('total_amount');
        $umrahSales = (float) UmrahBooking::sum('package_amount');
        $hotelSales = (float) HotelBooking::sum('total_amount');

        $totalSales = $visaSales + $flightSales + $umrahSales + $hotelSales;

        $visaPayments = (float) Payment::sum('amount');
        $flightPayments = (float) FlightPayment::sum('amount');
        $umrahPayments = (float) UmrahPayment::sum('amount');
        $hotelPayments = (float) HotelPayment::sum('amount');

        $paymentsReceived = $visaPayments + $flightPayments + $umrahPayments + $hotelPayments;

        $expenses = (float) Expense::sum('amount');

        $refunds = (float) Refund::where('status', 'Paid')->sum('refund_amount');

        $cashOut = $expenses + $refunds;

        $netProfit = $paymentsReceived - $cashOut;

        $visaBalance = (float) VisaApplication::sum('balance_amount');
        $flightBalance = (float) FlightBooking::sum('balance_amount');
        $umrahBalance = (float) UmrahBooking::sum('balance_amount');
        $hotelBalance = (float) HotelBooking::sum('balance_amount');

        $outstandingBalance = $visaBalance + $flightBalance + $umrahBalance + $hotelBalance;

        return [
            Stat::make('Total Visa Applications', number_format(VisaApplication::count()))
                ->description('All visa application records')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('Flight Bookings', number_format(FlightBooking::count()))
                ->description('All flight booking records')
                ->descriptionIcon('heroicon-m-paper-airplane')
                ->color('info'),

            Stat::make('Umrah Bookings', number_format(UmrahBooking::count()))
                ->description('All Umrah booking records')
                ->descriptionIcon('heroicon-m-cube')
                ->color('success'),

            Stat::make('Hotel Bookings', number_format(HotelBooking::count()))
                ->description('All hotel booking records')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('warning'),

            Stat::make('Total Customers', number_format(Customer::count()))
                ->description('All customer records')
                ->descriptionIcon('heroicon-m-users')
                ->color('gray'),

            Stat::make('Visa Sales', 'PKR ' . number_format($visaSales, 2))
                ->description('Total visa amount')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),

            Stat::make('Flight Sales', 'PKR ' . number_format($flightSales, 2))
                ->description('Total flight amount')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),

            Stat::make('Umrah Sales', 'PKR ' . number_format($umrahSales, 2))
                ->description('Total Umrah amount')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Hotel Sales', 'PKR ' . number_format($hotelSales, 2))
                ->description('Total hotel amount')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),

            Stat::make('Total Sales', 'PKR ' . number_format($totalSales, 2))
                ->description('Visa + Flight + Umrah + Hotel')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('success'),

            Stat::make('Payments Received', 'PKR ' . number_format($paymentsReceived, 2))
                ->description('All received payments')
                ->descriptionIcon('heroicon-m-arrow-down-circle')
                ->color('success'),

            Stat::make('Total Expenses', 'PKR ' . number_format($expenses, 2))
                ->description('Business expenses only')
                ->descriptionIcon('heroicon-m-arrow-up-circle')
                ->color('danger'),

            Stat::make('Total Refunds', 'PKR ' . number_format($refunds, 2))
                ->description('Paid refunds only')
                ->descriptionIcon('heroicon-m-arrow-uturn-left')
                ->color('warning'),

            Stat::make('Total Cash Out', 'PKR ' . number_format($cashOut, 2))
                ->description('Expenses + paid refunds')
                ->descriptionIcon('heroicon-m-arrow-up-circle')
                ->color('danger'),

            Stat::make('Net Profit', 'PKR ' . number_format($netProfit, 2))
                ->description('Payments received - cash out')
                ->descriptionIcon('heroicon-m-chart-pie')
                ->color($netProfit >= 0 ? 'success' : 'danger'),

            Stat::make('Outstanding Balance', 'PKR ' . number_format($outstandingBalance, 2))
                ->description('All services pending balance')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('warning'),

            Stat::make('Processing Visas', number_format(VisaApplication::where('status', 'Processing')->count()))
                ->description('Visa applications in processing')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Passport Pending', number_format(VisaApplication::where('passport_status', 'Pending')->count()))
                ->description('Passports not received yet')
                ->descriptionIcon('heroicon-m-identification')
                ->color('danger'),
        ];
    }
}
