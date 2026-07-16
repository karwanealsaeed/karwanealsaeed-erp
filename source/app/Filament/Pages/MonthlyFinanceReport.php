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

class MonthlyFinanceReport extends Page
{

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-calendar';


    protected static string | \UnitEnum | null $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Monthly Finance Report';
    protected static ?string $title = 'Monthly Finance Report';

    protected string $view = 'filament.pages.monthly-finance-report';

    public ?string $reportMonth = null;

    public function mount(): void
    {
        $this->reportMonth = now()->format('Y-m');
    }

    public function getReportData(): array
    {
        $month = $this->reportMonth ?: now()->format('Y-m');

        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = Carbon::parse($month . '-01')->endOfMonth();

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
            'report_month' => $month,
            'formatted_month' => $startDate->format('F Y'),
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


