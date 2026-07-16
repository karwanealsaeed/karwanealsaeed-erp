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

class DateRangeFinanceReport extends Page
{

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-arrows-right-left';


    protected static string | \UnitEnum | null $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationLabel = 'Date Range Finance Report';
    protected static ?string $title = 'Date Range Finance Report';

    protected string $view = 'filament.pages.date-range-finance-report';

    public ?string $fromDate = null;

    public ?string $toDate = null;

    public function mount(): void
    {
        $this->fromDate = now()->startOfMonth()->format('Y-m-d');
        $this->toDate = now()->format('Y-m-d');
    }

    public function getReportData(): array
    {
        $fromDate = $this->fromDate ?: now()->startOfMonth()->format('Y-m-d');
        $toDate = $this->toDate ?: now()->format('Y-m-d');

        $startDate = Carbon::parse($fromDate)->startOfDay();
        $endDate = Carbon::parse($toDate)->endOfDay();

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
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'formatted_from_date' => $startDate->format('d M Y'),
            'formatted_to_date' => $endDate->format('d M Y'),

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


