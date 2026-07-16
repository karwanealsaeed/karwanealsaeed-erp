<?php

namespace App\Filament\Pages;

use App\Models\FlightPayment;
use App\Models\HotelPayment;
use App\Models\Payment;
use App\Models\UmrahPayment;
use Carbon\Carbon;
use Filament\Pages\Page;

class PaymentMethodReport extends Page
{

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-credit-card';


    protected static string | \UnitEnum | null $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 13;
    protected static ?string $navigationLabel = 'Payment Method Report';
    protected static ?string $title = 'Payment Method Report';

    protected string $view = 'filament.pages.payment-method-report';

    public ?string $fromDate = null;

    public ?string $toDate = null;

    public function mount(): void
    {
        $this->fromDate = now()->startOfMonth()->format('Y-m-d');
        $this->toDate = now()->format('Y-m-d');
    }

    public function getReportData(): array
    {
        $fromDate = Carbon::parse($this->fromDate ?? now()->startOfMonth())->startOfDay();
        $toDate = Carbon::parse($this->toDate ?? now())->endOfDay();

        if ($fromDate->greaterThan($toDate)) {
            [$fromDate, $toDate] = [
                $toDate->copy()->startOfDay(),
                $fromDate->copy()->endOfDay(),
            ];
        }

        $methods = [
            'Cash',
            'Bank',
            'JazzCash',
            'EasyPaisa',
            'Credit Card',
            'Cheque',
        ];

        $rows = [];

        foreach ($methods as $method) {
            $visaAmount = Payment::where('payment_method', $method)
                ->whereBetween('payment_date', [$fromDate, $toDate])
                ->sum('amount');

            $flightAmount = FlightPayment::where('payment_method', $method)
                ->whereBetween('payment_date', [$fromDate, $toDate])
                ->sum('amount');

            $umrahAmount = UmrahPayment::where('payment_method', $method)
                ->whereBetween('payment_date', [$fromDate, $toDate])
                ->sum('amount');

            $hotelAmount = HotelPayment::where('payment_method', $method)
                ->whereBetween('payment_date', [$fromDate, $toDate])
                ->sum('amount');

            $totalAmount = $visaAmount + $flightAmount + $umrahAmount + $hotelAmount;

            $visaCount = Payment::where('payment_method', $method)
                ->whereBetween('payment_date', [$fromDate, $toDate])
                ->count();

            $flightCount = FlightPayment::where('payment_method', $method)
                ->whereBetween('payment_date', [$fromDate, $toDate])
                ->count();

            $umrahCount = UmrahPayment::where('payment_method', $method)
                ->whereBetween('payment_date', [$fromDate, $toDate])
                ->count();

            $hotelCount = HotelPayment::where('payment_method', $method)
                ->whereBetween('payment_date', [$fromDate, $toDate])
                ->count();

            $totalCount = $visaCount + $flightCount + $umrahCount + $hotelCount;

            $rows[] = [
                'method' => $method,
                'visa_amount' => (float) $visaAmount,
                'flight_amount' => (float) $flightAmount,
                'umrah_amount' => (float) $umrahAmount,
                'hotel_amount' => (float) $hotelAmount,
                'total_amount' => (float) $totalAmount,
                'total_count' => $totalCount,
            ];
        }

        return [
            'from_date' => $fromDate->format('Y-m-d'),
            'to_date' => $toDate->format('Y-m-d'),
            'rows' => $rows,
            'total_records' => collect($rows)->sum('total_count'),
            'visa_total' => collect($rows)->sum('visa_amount'),
            'flight_total' => collect($rows)->sum('flight_amount'),
            'umrah_total' => collect($rows)->sum('umrah_amount'),
            'hotel_total' => collect($rows)->sum('hotel_amount'),
            'grand_total' => collect($rows)->sum('total_amount'),
        ];
    }
    public static function canAccess(): bool
    {
        return auth()->user()?->canViewReports() ?? false;
    }
}


