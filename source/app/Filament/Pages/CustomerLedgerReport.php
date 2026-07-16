<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Models\FlightBooking;
use App\Models\FlightPayment;
use App\Models\HotelBooking;
use App\Models\HotelPayment;
use App\Models\Payment;
use App\Models\UmrahBooking;
use App\Models\UmrahPayment;
use App\Models\VisaApplication;
use Filament\Pages\Page;

class CustomerLedgerReport extends Page
{

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-book-open';


    protected static string | \UnitEnum | null $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 12;
    protected static ?string $navigationLabel = 'Customer Ledger Report';
    protected static ?string $title = 'Customer Ledger Report';

    protected string $view = 'filament.pages.customer-ledger-report';

    public ?int $customerId = null;

    public function getCustomers()
    {
        return Customer::orderBy('name')->get();
    }

    public function getReportData(): array
    {
        $customer = null;

        if ($this->customerId) {
            $customer = Customer::find($this->customerId);
        }

        if (! $customer) {
            return [
                'customer' => null,
                'rows' => [],
                'transactions' => [],
                'total_sales' => 0,
                'total_paid' => 0,
                'total_balance' => 0,
            ];
        }

        $visaApplications = VisaApplication::where('customer_id', $customer->id)->get();
        $flightBookings = FlightBooking::where('customer_id', $customer->id)->get();
        $umrahBookings = UmrahBooking::where('customer_id', $customer->id)->get();
        $hotelBookings = HotelBooking::where('customer_id', $customer->id)->get();

        $rows = [
            [
                'service' => 'Visa',
                'count' => $visaApplications->count(),
                'total_amount' => (float) $visaApplications->sum('total_amount'),
                'paid_amount' => (float) $visaApplications->sum('paid_amount'),
                'balance_amount' => (float) $visaApplications->sum('balance_amount'),
            ],
            [
                'service' => 'Flight',
                'count' => $flightBookings->count(),
                'total_amount' => (float) $flightBookings->sum('total_amount'),
                'paid_amount' => (float) $flightBookings->sum('paid_amount'),
                'balance_amount' => (float) $flightBookings->sum('balance_amount'),
            ],
            [
                'service' => 'Umrah',
                'count' => $umrahBookings->count(),
                'total_amount' => (float) $umrahBookings->sum('package_amount'),
                'paid_amount' => (float) $umrahBookings->sum('paid_amount'),
                'balance_amount' => (float) $umrahBookings->sum('balance_amount'),
            ],
            [
                'service' => 'Hotel',
                'count' => $hotelBookings->count(),
                'total_amount' => (float) $hotelBookings->sum('total_amount'),
                'paid_amount' => (float) $hotelBookings->sum('paid_amount'),
                'balance_amount' => (float) $hotelBookings->sum('balance_amount'),
            ],
        ];

        $transactions = [];

        $visaIds = $visaApplications->pluck('id')->toArray();
        $flightIds = $flightBookings->pluck('id')->toArray();
        $umrahIds = $umrahBookings->pluck('id')->toArray();
        $hotelIds = $hotelBookings->pluck('id')->toArray();

        $visaPayments = Payment::with('visaApplication')
            ->whereIn('visa_application_id', $visaIds)
            ->get();

        foreach ($visaPayments as $payment) {
            $transactions[] = [
                'date' => $payment->payment_date,
                'service' => 'Visa',
                'reference' => $payment->visaApplication?->application_no ?? $payment->reference_no ?? '-',
                'method' => $payment->payment_method ?? '-',
                'amount' => (float) $payment->amount,
                'notes' => $payment->notes ?? '-',
            ];
        }

        $flightPayments = FlightPayment::with('flightBooking')
            ->whereIn('flight_booking_id', $flightIds)
            ->get();

        foreach ($flightPayments as $payment) {
            $transactions[] = [
                'date' => $payment->payment_date,
                'service' => 'Flight',
                'reference' => $payment->flightBooking?->booking_no ?? $payment->reference_no ?? '-',
                'method' => $payment->method ?? '-',
                'amount' => (float) $payment->amount,
                'notes' => $payment->notes ?? '-',
            ];
        }

        $umrahPayments = UmrahPayment::with('umrahBooking')
            ->whereIn('umrah_booking_id', $umrahIds)
            ->get();

        foreach ($umrahPayments as $payment) {
            $transactions[] = [
                'date' => $payment->payment_date,
                'service' => 'Umrah',
                'reference' => $payment->umrahBooking?->booking_no ?? $payment->reference_no ?? '-',
                'method' => $payment->method ?? '-',
                'amount' => (float) $payment->amount,
                'notes' => $payment->notes ?? '-',
            ];
        }

        $hotelPayments = HotelPayment::with('hotelBooking')
            ->whereIn('hotel_booking_id', $hotelIds)
            ->get();

        foreach ($hotelPayments as $payment) {
            $transactions[] = [
                'date' => $payment->payment_date,
                'service' => 'Hotel',
                'reference' => $payment->hotelBooking?->booking_no ?? $payment->reference_no ?? '-',
                'method' => $payment->method ?? '-',
                'amount' => (float) $payment->amount,
                'notes' => $payment->notes ?? '-',
            ];
        }

        $transactions = collect($transactions)
            ->sortByDesc('date')
            ->values()
            ->toArray();

        return [
            'customer' => $customer,
            'rows' => $rows,
            'transactions' => $transactions,
            'total_sales' => collect($rows)->sum('total_amount'),
            'total_paid' => collect($rows)->sum('paid_amount'),
            'total_balance' => collect($rows)->sum('balance_amount'),
        ];
    }
    public static function canAccess(): bool
    {
        return auth()->user()?->canViewReports() ?? false;
    }
}


