<?php

namespace App\Filament\Pages;

use App\Models\Expense;
use App\Models\FlightPayment;
use App\Models\HotelPayment;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\UmrahPayment;
use Carbon\Carbon;
use Filament\Pages\Page;

class DailyFinanceReport extends Page
{

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-calendar-days';


    protected static string | \UnitEnum | null $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Daily Finance Report';
    protected static ?string $title = 'Daily Finance Report';

    protected string $view = 'filament.pages.daily-finance-report';

    public ?string $reportDate = null;

    public function mount(): void
    {
        $this->reportDate = now()->format('Y-m-d');
    }

    public function getReportData(): array
    {
        $date = $this->reportDate ?: now()->format('Y-m-d');

        $visaPayments = (float) Payment::whereDate('payment_date', $date)->sum('amount');
        $flightPayments = (float) FlightPayment::whereDate('payment_date', $date)->sum('amount');
        $umrahPayments = (float) UmrahPayment::whereDate('payment_date', $date)->sum('amount');
        $hotelPayments = (float) HotelPayment::whereDate('payment_date', $date)->sum('amount');

        $totalCollection = $visaPayments + $flightPayments + $umrahPayments + $hotelPayments;

        $expenses = (float) Expense::whereDate('expense_date', $date)->sum('amount');

        $refunds = (float) Refund::whereDate('refund_date', $date)
            ->where('status', 'Paid')
            ->sum('refund_amount');

        $totalCashOut = $expenses + $refunds;

        $netProfit = $totalCollection - $totalCashOut;

        return [
            'report_date' => $date,
            'formatted_date' => Carbon::parse($date)->format('d M Y'),

            'visa_payments' => $visaPayments,
            'flight_payments' => $flightPayments,
            'umrah_payments' => $umrahPayments,
            'hotel_payments' => $hotelPayments,

            'total_collection' => $totalCollection,
            'total_expenses' => $expenses,
            'total_refunds' => $refunds,
            'total_cash_out' => $totalCashOut,
            'net_profit' => $netProfit,

            'rows' => [
                [
                    'service' => 'Visa Payments',
                    'amount' => $visaPayments,
                ],
                [
                    'service' => 'Flight Payments',
                    'amount' => $flightPayments,
                ],
                [
                    'service' => 'Umrah Payments',
                    'amount' => $umrahPayments,
                ],
                [
                    'service' => 'Hotel Payments',
                    'amount' => $hotelPayments,
                ],
            ],
        ];
    }
    public static function canAccess(): bool
    {
        return auth()->user()?->canViewReports() ?? false;
    }
}


