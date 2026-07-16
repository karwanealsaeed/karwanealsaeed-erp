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

class DailyCashbookReport extends Page
{

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';


    protected static string | \UnitEnum | null $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationLabel = 'Daily Cashbook Report';
    protected static ?string $title = 'Daily Cashbook Report';

    protected string $view = 'filament.pages.daily-cashbook-report';

    public ?string $reportDate = null;

    public function mount(): void
    {
        $this->reportDate = now()->format('Y-m-d');
    }

    public function getReportData(): array
    {
        $date = $this->reportDate ?: now()->format('Y-m-d');

        $visaPayments = Payment::with('visaApplication.customer')
            ->whereDate('payment_date', $date)
            ->get();

        $flightPayments = FlightPayment::with('flightBooking.customer')
            ->whereDate('payment_date', $date)
            ->get();

        $umrahPayments = UmrahPayment::with('umrahBooking.customer')
            ->whereDate('payment_date', $date)
            ->get();

        $hotelPayments = HotelPayment::with('hotelBooking.customer')
            ->whereDate('payment_date', $date)
            ->get();

        $expenses = Expense::with('category')
            ->whereDate('expense_date', $date)
            ->get();

        $refunds = Refund::with('customer')
            ->whereDate('refund_date', $date)
            ->where('status', 'Paid')
            ->get();

        $transactions = [];

        foreach ($visaPayments as $payment) {
            $transactions[] = [
                'date' => $payment->payment_date,
                'type' => 'Cash In',
                'service' => 'Visa Payment',
                'reference' => $payment->visaApplication?->application_no ?? $payment->reference_no ?? '-',
                'customer' => $payment->visaApplication?->customer?->name ?? '-',
                'method' => $payment->payment_method ?? '-',
                'description' => $payment->notes ?? 'Visa payment received',
                'cash_in' => (float) $payment->amount,
                'cash_out' => 0,
            ];
        }

        foreach ($flightPayments as $payment) {
            $transactions[] = [
                'date' => $payment->payment_date,
                'type' => 'Cash In',
                'service' => 'Flight Payment',
                'reference' => $payment->flightBooking?->booking_no ?? $payment->reference_no ?? '-',
                'customer' => $payment->flightBooking?->customer?->name ?? '-',
                'method' => $payment->payment_method ?? $payment->method ?? '-',
                'description' => $payment->notes ?? 'Flight payment received',
                'cash_in' => (float) $payment->amount,
                'cash_out' => 0,
            ];
        }

        foreach ($umrahPayments as $payment) {
            $transactions[] = [
                'date' => $payment->payment_date,
                'type' => 'Cash In',
                'service' => 'Umrah Payment',
                'reference' => $payment->umrahBooking?->booking_no ?? $payment->reference_no ?? '-',
                'customer' => $payment->umrahBooking?->customer?->name ?? '-',
                'method' => $payment->payment_method ?? $payment->method ?? '-',
                'description' => $payment->notes ?? 'Umrah payment received',
                'cash_in' => (float) $payment->amount,
                'cash_out' => 0,
            ];
        }

        foreach ($hotelPayments as $payment) {
            $transactions[] = [
                'date' => $payment->payment_date,
                'type' => 'Cash In',
                'service' => 'Hotel Payment',
                'reference' => $payment->hotelBooking?->booking_no ?? $payment->reference_no ?? '-',
                'customer' => $payment->hotelBooking?->customer?->name ?? '-',
                'method' => $payment->payment_method ?? $payment->method ?? '-',
                'description' => $payment->notes ?? 'Hotel payment received',
                'cash_in' => (float) $payment->amount,
                'cash_out' => 0,
            ];
        }

        foreach ($expenses as $expense) {
            $transactions[] = [
                'date' => $expense->expense_date,
                'type' => 'Cash Out',
                'service' => 'Expense',
                'reference' => $expense->expense_no ?? $expense->reference_no ?? '-',
                'customer' => '-',
                'method' => $expense->payment_method ?? '-',
                'description' => $expense->description ?? $expense->category?->name ?? 'Expense paid',
                'cash_in' => 0,
                'cash_out' => (float) $expense->amount,
            ];
        }

        foreach ($refunds as $refund) {
            $transactions[] = [
                'date' => $refund->refund_date,
                'type' => 'Cash Out',
                'service' => 'Refund',
                'reference' => $refund->refund_no ?? $refund->service_reference ?? '-',
                'customer' => $refund->customer?->name ?? '-',
                'method' => $refund->payment_method ?? '-',
                'description' => ($refund->service_type ?? 'Refund') . ' - ' . ($refund->service_reference ?? '-') . ' - ' . ($refund->reason ?? 'Refund paid'),
                'cash_in' => 0,
                'cash_out' => (float) $refund->refund_amount,
            ];
        }

        $transactions = collect($transactions)
            ->sortBy([
                ['type', 'asc'],
                ['service', 'asc'],
            ])
            ->values()
            ->toArray();

        $visaTotal = (float) $visaPayments->sum('amount');
        $flightTotal = (float) $flightPayments->sum('amount');
        $umrahTotal = (float) $umrahPayments->sum('amount');
        $hotelTotal = (float) $hotelPayments->sum('amount');
        $expenseTotal = (float) $expenses->sum('amount');
        $refundTotal = (float) $refunds->sum('refund_amount');

        $totalCashIn = $visaTotal + $flightTotal + $umrahTotal + $hotelTotal;
        $totalCashOut = $expenseTotal + $refundTotal;
        $netCash = $totalCashIn - $totalCashOut;

        return [
            'report_date' => $date,
            'formatted_date' => Carbon::parse($date)->format('d M Y'),

            'visa_payments' => $visaTotal,
            'flight_payments' => $flightTotal,
            'umrah_payments' => $umrahTotal,
            'hotel_payments' => $hotelTotal,
            'expenses' => $expenseTotal,
            'refunds' => $refundTotal,

            'total_cash_in' => $totalCashIn,
            'total_cash_out' => $totalCashOut,
            'net_cash' => $netCash,

            'transaction_count' => count($transactions),
            'transactions' => $transactions,
        ];
    }
    public static function canAccess(): bool
    {
        return auth()->user()?->canViewReports() ?? false;
    }
}


