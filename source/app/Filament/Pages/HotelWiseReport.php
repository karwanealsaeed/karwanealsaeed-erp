<?php

namespace App\Filament\Pages;

use App\Models\HotelBooking;
use Filament\Pages\Page;

class HotelWiseReport extends Page
{

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-office-2';


    protected static string | \UnitEnum | null $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 50;
    protected static ?string $navigationLabel = 'Hotel Wise Report';
    protected static ?string $title = 'Hotel Wise Report';

    protected string $view = 'filament.pages.hotel-wise-report';

    public function getReportData(): array
    {
        $rows = [];

        $bookings = HotelBooking::with('country')
            ->get()
            ->groupBy(function ($booking) {
                return strtolower($booking->hotel_name) . '-' . strtolower($booking->city) . '-' . $booking->country_id;
            });

        foreach ($bookings as $hotelBookings) {
            $firstBooking = $hotelBookings->first();

            $rows[] = [
                'hotel_name' => $firstBooking->hotel_name ?? '-',
                'city' => $firstBooking->city ?? '-',
                'country' => $firstBooking->country?->name ?? '-',

                'total_bookings' => $hotelBookings->count(),
                'total_rooms' => $hotelBookings->sum('no_of_rooms'),
                'total_persons' => $hotelBookings->sum('no_of_persons'),

                'total_sales' => (float) $hotelBookings->sum('total_amount'),
                'paid_amount' => (float) $hotelBookings->sum('paid_amount'),
                'balance_amount' => (float) $hotelBookings->sum('balance_amount'),

                'draft' => $hotelBookings->where('status', 'Draft')->count(),
                'booked' => $hotelBookings->where('status', 'Booked')->count(),
                'confirmed' => $hotelBookings->where('status', 'Confirmed')->count(),
                'cancelled' => $hotelBookings->where('status', 'Cancelled')->count(),
                'completed' => $hotelBookings->where('status', 'Completed')->count(),
            ];
        }

        return [
            'rows' => $rows,

            'total_hotels' => count($rows),
            'total_bookings' => collect($rows)->sum('total_bookings'),
            'total_rooms' => collect($rows)->sum('total_rooms'),
            'total_persons' => collect($rows)->sum('total_persons'),

            'total_sales' => collect($rows)->sum('total_sales'),
            'total_paid' => collect($rows)->sum('paid_amount'),
            'total_balance' => collect($rows)->sum('balance_amount'),

            'total_draft' => collect($rows)->sum('draft'),
            'total_booked' => collect($rows)->sum('booked'),
            'total_confirmed' => collect($rows)->sum('confirmed'),
            'total_cancelled' => collect($rows)->sum('cancelled'),
            'total_completed' => collect($rows)->sum('completed'),
        ];
    }
    public static function canAccess(): bool
    {
        return auth()->user()?->canViewReports() ?? false;
    }
}


