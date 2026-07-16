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

class YearlyFinanceReport extends Page
{

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar';


    protected static string | \UnitEnum | null $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'Yearly Finance Report';
    protected static ?string $title = 'Yearly Finance Report';

    protected string $view = 'filament.pages.yearly-finance-report';

    public ?string $reportYear = null;

    public function mount(): void
    {
        $this->reportYear = now()->format('Y');
    }

    public function getReportData(): array
    {
        $year = $this->reportYear ?: now()->format('Y');

        $startDate = Carbon::parse($year . '-01-01')->startOfYear();
        $endDate = Carbon::parse($year . '-01-01')->endOfYear();

        $visaPayments = (float) Payment::whereBetween('payment_date', [$startDate, $endDate])->sum('amount');
        $flightPayments = (float) FlightPayment::whereBetween('payment_date', [$startDate, $endDate])->sum('amount');
        $umrahPayments = (float) UmrahPayment::whereBetween('payment_date', [$startDate, $endDate])->sum('amount');
        $hotelPayments = (float) HotelPayment::whereBetween('payment_date', [$startDate, $endDate])->sum('amount');

        $totalCollection = $visaPayments + $flightPayments + $umrahPayments + $hotelPayments;

        $expenses = (float) Expense::whereBetween('expense_date', [$startDate, $endDate])->sum('amount');

        $refunds = (float) Refund::whereBetween('refund_date', [$startDate, $endDate])
            ->where('status', 'Paid')
            ->sum('refund_amount');

        $totalCashOut = $expenses + $refunds;

        $netProfit = $totalCollection - $totalCashOut;

        return [
            'report_year' => $year,
            'formatted_year' => $year,
            'start_date' => $startDate->format('d M Y'),
            'end_date' => $endDate->format('d M Y'),

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


