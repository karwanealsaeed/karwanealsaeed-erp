<?php

namespace App\Filament\Pages;

use App\Models\UmrahBooking;
use Filament\Pages\Page;

class UmrahPackageWiseReport extends Page
{

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-library';


    protected static string | \UnitEnum | null $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 40;
    protected static ?string $navigationLabel = 'Umrah Package Wise Report';
    protected static ?string $title = 'Umrah Package Wise Report';

    protected string $view = 'filament.pages.umrah-package-wise-report';

    public function getReportData(): array
    {
        $rows = [];

        $bookings = UmrahBooking::get()
            ->groupBy('package_name');

        foreach ($bookings as $packageName => $packageBookings) {
            $rows[] = [
                'package_name' => $packageName ?: '-',

                'total_bookings' => $packageBookings->count(),
                'total_persons' => $packageBookings->sum('no_of_persons'),

                'total_sales' => (float) $packageBookings->sum('package_amount'),
                'paid_amount' => (float) $packageBookings->sum('paid_amount'),
                'balance_amount' => (float) $packageBookings->sum('balance_amount'),

                'draft' => $packageBookings->where('status', 'Draft')->count(),
                'booked' => $packageBookings->where('status', 'Booked')->count(),
                'processing' => $packageBookings->where('status', 'Processing')->count(),
                'confirmed' => $packageBookings->where('status', 'Confirmed')->count(),
                'cancelled' => $packageBookings->where('status', 'Cancelled')->count(),
                'completed' => $packageBookings->where('status', 'Completed')->count(),
            ];
        }

        return [
            'rows' => $rows,

            'total_packages' => count($rows),
            'total_bookings' => collect($rows)->sum('total_bookings'),
            'total_persons' => collect($rows)->sum('total_persons'),

            'total_sales' => collect($rows)->sum('total_sales'),
            'total_paid' => collect($rows)->sum('paid_amount'),
            'total_balance' => collect($rows)->sum('balance_amount'),

            'total_draft' => collect($rows)->sum('draft'),
            'total_booked' => collect($rows)->sum('booked'),
            'total_processing' => collect($rows)->sum('processing'),
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


