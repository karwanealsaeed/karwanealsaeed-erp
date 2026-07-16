<?php

namespace App\Filament\Pages;

use App\Models\FlightBooking;
use App\Models\HotelBooking;
use App\Models\UmrahBooking;
use App\Models\VisaApplication;
use Filament\Pages\Page;

class BookingStatusReport extends Page
{

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-list';


    protected static string | \UnitEnum | null $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 16;
    protected static ?string $navigationLabel = 'Booking Status Report';
    protected static ?string $title = 'Booking Status Report';

    protected string $view = 'filament.pages.booking-status-report';

    public function getReportData(): array
    {
        $visaStatuses = [
            'Draft',
            'Applied',
            'Processing',
            'Approved',
            'Rejected',
            'Delivered',
        ];

        $flightStatuses = [
            'Draft',
            'Booked',
            'Ticket Issued',
            'Cancelled',
            'Refunded',
        ];

        $umrahStatuses = [
            'Draft',
            'Booked',
            'Processing',
            'Confirmed',
            'Cancelled',
            'Completed',
        ];

        $hotelStatuses = [
            'Draft',
            'Booked',
            'Confirmed',
            'Cancelled',
            'Completed',
        ];

        $visaRows = $this->makeRows(VisaApplication::class, $visaStatuses);
        $flightRows = $this->makeRows(FlightBooking::class, $flightStatuses);
        $umrahRows = $this->makeRows(UmrahBooking::class, $umrahStatuses);
        $hotelRows = $this->makeRows(HotelBooking::class, $hotelStatuses);

        $visaTotal = collect($visaRows)->sum('count');
        $flightTotal = collect($flightRows)->sum('count');
        $umrahTotal = collect($umrahRows)->sum('count');
        $hotelTotal = collect($hotelRows)->sum('count');

        return [
            'visa_rows' => $visaRows,
            'flight_rows' => $flightRows,
            'umrah_rows' => $umrahRows,
            'hotel_rows' => $hotelRows,

            'visa_total' => $visaTotal,
            'flight_total' => $flightTotal,
            'umrah_total' => $umrahTotal,
            'hotel_total' => $hotelTotal,

            'grand_total' => $visaTotal + $flightTotal + $umrahTotal + $hotelTotal,
        ];
    }

    protected function makeRows(string $modelClass, array $statuses): array
    {
        $total = $modelClass::count();

        $rows = [];

        foreach ($statuses as $status) {
            $count = $modelClass::where('status', $status)->count();

            $rows[] = [
                'status' => $status,
                'count' => $count,
                'percentage' => $total > 0 ? round(($count / $total) * 100, 2) : 0,
            ];
        }

        return $rows;
    }
    public static function canAccess(): bool
    {
        return auth()->user()?->canViewReports() ?? false;
    }
}


