<?php

namespace App\Filament\Pages;

use App\Models\UmrahPackage;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class UmrahPackagePoster extends Page
{
    protected string $view = 'filament.pages.umrah-package-poster';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-photo';

    protected static string | \UnitEnum | null $navigationGroup = 'Bookings';

    protected static ?int $navigationSort = 9;

    protected static ?string $navigationLabel = 'Umrah Package Poster';

    protected static ?string $title = 'Umrah Package Poster Generator';

    public static function canAccess(): bool
    {
        return (auth()->user()?->isAdmin() ?? false)
            || (auth()->user()?->hasPermission('umrah_bookings.view') ?? false);
    }

    public function getPackages(): Collection
    {
        $query = UmrahPackage::query()
            ->with(['umrahGroupFlight', 'makkahHotel', 'madinaHotel', 'transportRoute'])
            ->where('status', 'Available');

        $search = trim((string) request('search'));

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search): void {
                $subQuery
                    ->where('title', 'like', '%' . $search . '%')
                    ->orWhere('package_no', 'like', '%' . $search . '%')
                    ->orWhere('group_code', 'like', '%' . $search . '%')
                    ->orWhere('airline', 'like', '%' . $search . '%')
                    ->orWhere('flight_route', 'like', '%' . $search . '%')
                    ->orWhere('makkah_hotel', 'like', '%' . $search . '%')
                    ->orWhere('madina_hotel', 'like', '%' . $search . '%');
            });
        }

        if (filled(request('airline'))) {
            $query->where('airline', request('airline'));
        }

        if (filled(request('route'))) {
            $query->where('flight_route', request('route'));
        }

        if (filled(request('duration'))) {
            $duration = request('duration');

            if ($duration === '1-10') {
                $query->whereBetween('duration_nights', [1, 10]);
            }

            if ($duration === '11-20') {
                $query->whereBetween('duration_nights', [11, 20]);
            }

            if ($duration === '21-30') {
                $query->whereBetween('duration_nights', [21, 30]);
            }

            if ($duration === '31+') {
                $query->where('duration_nights', '>=', 31);
            }
        }

        return $query
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->limit(60)
            ->get();
    }

    public function getAirlines(): Collection
    {
        return UmrahPackage::query()
            ->where('status', 'Available')
            ->whereNotNull('airline')
            ->where('airline', '!=', '')
            ->distinct()
            ->orderBy('airline')
            ->pluck('airline');
    }

    public function getRoutes(): Collection
    {
        return UmrahPackage::query()
            ->where('status', 'Available')
            ->whereNotNull('flight_route')
            ->where('flight_route', '!=', '')
            ->distinct()
            ->orderBy('flight_route')
            ->pluck('flight_route');
    }
}