<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\UmrahPackage;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicUmrahPackageController extends Controller
{
    public function index(Request $request): View
    {
        $packagesQuery = UmrahPackage::query()
            ->with(['umrahGroupFlight', 'makkahHotel', 'madinaHotel', 'transportRoute'])
            ->where('status', 'Available');

        $packagesQuery
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search')->toString();

                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->where('title', 'like', '%' . $search . '%')
                        ->orWhere('package_no', 'like', '%' . $search . '%')
                        ->orWhere('group_code', 'like', '%' . $search . '%')
                        ->orWhere('makkah_hotel', 'like', '%' . $search . '%')
                        ->orWhere('madina_hotel', 'like', '%' . $search . '%')
                        ->orWhere('flight_route', 'like', '%' . $search . '%');
                });
            })
            ->when($request->filled('airline'), function ($query) use ($request): void {
                $query->where('airline', $request->string('airline')->toString());
            })
            ->when($request->filled('route'), function ($query) use ($request): void {
                $query->where('flight_route', $request->string('route')->toString());
            })
            ->when($request->filled('duration'), function ($query) use ($request): void {
                $duration = $request->string('duration')->toString();

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
            });

        match ($request->string('sort')->toString()) {
            'price_low' => $packagesQuery->orderBy('sharing_price'),
            'price_high' => $packagesQuery->orderByDesc('sharing_price'),
            'duration_low' => $packagesQuery->orderBy('duration_nights'),
            'duration_high' => $packagesQuery->orderByDesc('duration_nights'),
            'latest' => $packagesQuery->orderByDesc('id'),
            default => $packagesQuery
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->orderByDesc('id'),
        };

        $packages = $packagesQuery->get();

        $airlines = UmrahPackage::query()
            ->where('status', 'Available')
            ->whereNotNull('airline')
            ->distinct()
            ->orderBy('airline')
            ->pluck('airline');

        $routes = UmrahPackage::query()
            ->where('status', 'Available')
            ->whereNotNull('flight_route')
            ->distinct()
            ->orderBy('flight_route')
            ->pluck('flight_route');

        return view('website.umrah-packages', [
            'company' => CompanySetting::current(),
            'packages' => $packages,
            'airlines' => $airlines,
            'routes' => $routes,
            'title' => 'Umrah Packages | Karwan E Al Saeed',
            'description' => 'View latest Umrah packages by Karwan E Al Saeed Travel & Tours.',
        ]);
    }

    public function show(UmrahPackage $umrahPackage): View
    {
        $umrahPackage->load(['umrahGroupFlight', 'makkahHotel', 'madinaHotel', 'transportRoute']);

        abort_unless($umrahPackage->status === 'Available', 404);

        return view('website.umrah-package-detail', [
            'company' => CompanySetting::current(),
            'package' => $umrahPackage,
            'title' => $umrahPackage->title . ' | Karwan E Al Saeed',
            'description' => $umrahPackage->subtitle ?: 'Umrah package details by Karwan E Al Saeed Travel & Tours.',
        ]);
    }
}