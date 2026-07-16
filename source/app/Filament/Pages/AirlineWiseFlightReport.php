<?php

namespace App\Filament\Pages;

use App\Models\Airline;
use App\Models\FlightBooking;
use Filament\Pages\Page;

class AirlineWiseFlightReport extends Page
{

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-paper-airplane';


    protected static string | \UnitEnum | null $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 30;
    protected static ?string $navigationLabel = 'Airline Wise Flight Report';
    protected static ?string $title = 'Airline Wise Flight Report';

    protected string $view = 'filament.pages.airline-wise-flight-report';

    public function getReportData(): array
    {
        $rows = [];

        $airlines = Airline::orderBy('name')->get();

        foreach ($airlines as $airline) {
            $query = FlightBooking::where('airline_id', $airline->id);

            $totalBookings = (clone $query)->count();

            if ($totalBookings <= 0) {
                continue;
            }

            $totalSales = (clone $query)->sum('total_amount');
            $paidAmount = (clone $query)->sum('paid_amount');
            $balanceAmount = (clone $query)->sum('balance_amount');

            $draft = (clone $query)->where('status', 'Draft')->count();
            $booked = (clone $query)->where('status', 'Booked')->count();
            $ticketIssued = (clone $query)->where('status', 'Ticket Issued')->count();
            $cancelled = (clone $query)->where('status', 'Cancelled')->count();
            $refunded = (clone $query)->where('status', 'Refunded')->count();

            $rows[] = [
                'airline_name' => $airline->name,
                'total_bookings' => $totalBookings,
                'total_sales' => (float) $totalSales,
                'paid_amount' => (float) $paidAmount,
                'balance_amount' => (float) $balanceAmount,
                'draft' => $draft,
                'booked' => $booked,
                'ticket_issued' => $ticketIssued,
                'cancelled' => $cancelled,
                'refunded' => $refunded,
            ];
        }

        return [
            'rows' => $rows,
            'total_airlines' => count($rows),
            'total_bookings' => collect($rows)->sum('total_bookings'),
            'total_sales' => collect($rows)->sum('total_sales'),
            'total_paid' => collect($rows)->sum('paid_amount'),
            'total_balance' => collect($rows)->sum('balance_amount'),
            'total_draft' => collect($rows)->sum('draft'),
            'total_booked' => collect($rows)->sum('booked'),
            'total_ticket_issued' => collect($rows)->sum('ticket_issued'),
            'total_cancelled' => collect($rows)->sum('cancelled'),
            'total_refunded' => collect($rows)->sum('refunded'),
        ];
    }
    public static function canAccess(): bool
    {
        return auth()->user()?->canViewReports() ?? false;
    }
}


