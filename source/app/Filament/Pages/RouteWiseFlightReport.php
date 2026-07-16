<?php

namespace App\Filament\Pages;

use App\Models\FlightBooking;
use Filament\Pages\Page;

class RouteWiseFlightReport extends Page
{

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-map';


    protected static string | \UnitEnum | null $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 31;
    protected static ?string $navigationLabel = 'Route Wise Flight Report';
    protected static ?string $title = 'Route Wise Flight Report';

    protected string $view = 'filament.pages.route-wise-flight-report';

    public function getReportData(): array
    {
        $rows = [];

        $bookings = FlightBooking::with(['fromAirport', 'toAirport'])
            ->get()
            ->groupBy(function ($booking) {
                return $booking->from_airport_id . '-' . $booking->to_airport_id;
            });

        foreach ($bookings as $routeBookings) {
            $firstBooking = $routeBookings->first();

            $fromAirport = $firstBooking->fromAirport?->name ?? '-';
            $toAirport = $firstBooking->toAirport?->name ?? '-';

            $totalBookings = $routeBookings->count();

            $rows[] = [
                'from_airport' => $fromAirport,
                'to_airport' => $toAirport,
                'route' => $fromAirport . ' → ' . $toAirport,

                'total_bookings' => $totalBookings,
                'total_sales' => (float) $routeBookings->sum('total_amount'),
                'paid_amount' => (float) $routeBookings->sum('paid_amount'),
                'balance_amount' => (float) $routeBookings->sum('balance_amount'),

                'draft' => $routeBookings->where('status', 'Draft')->count(),
                'booked' => $routeBookings->where('status', 'Booked')->count(),
                'ticket_issued' => $routeBookings->where('status', 'Ticket Issued')->count(),
                'cancelled' => $routeBookings->where('status', 'Cancelled')->count(),
                'refunded' => $routeBookings->where('status', 'Refunded')->count(),
            ];
        }

        return [
            'rows' => $rows,

            'total_routes' => count($rows),
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


