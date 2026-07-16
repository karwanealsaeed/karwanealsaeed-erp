<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\UmrahPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class UmrahPackagePosterPrintController extends Controller
{
    public function __invoke(Request $request): View
    {
        abort_unless(
            (auth()->user()?->isAdmin() ?? false)
            || (auth()->user()?->hasPermission('umrah_bookings.view') ?? false),
            403
        );

        $packages = $this->packages($request);
        $company = CompanySetting::current();

        return view('prints.umrah-package-poster', [
            'company' => $company,
            'packages' => $packages,
            'posterText' => $this->posterText($packages, $company),
        ]);
    }

    private function packages(Request $request): Collection
    {
        $query = UmrahPackage::query()
            ->with(['umrahGroupFlight', 'makkahHotel', 'madinaHotel', 'transportRoute'])
            ->where('status', 'Available');

        $selectedIds = collect($request->input('package_ids', []))
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->values()
            ->all();

        if (count($selectedIds) > 0) {
            return $query
                ->whereIn('id', $selectedIds)
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->get();
        }

        $search = trim((string) $request->input('search'));

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

        if ($request->filled('airline')) {
            $query->where('airline', $request->string('airline')->toString());
        }

        if ($request->filled('route')) {
            $query->where('flight_route', $request->string('route')->toString());
        }

        if ($request->filled('duration')) {
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
        }

        return $query
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->limit(24)
            ->get();
    }

    private function posterText(Collection $packages, CompanySetting $company): string
    {
        $lines = [];

        $lines[] = $company->company_name ?: 'Karwan E Al Saeed Travel & Tours';
        $lines[] = 'UMRAH PACKAGES';
        $lines[] = 'Contact: ' . ($company->mobile ?: '+92 301 2592342');
        $lines[] = 'Address: ' . ($company->address ?: 'Office # 90,91 College Road, Chichawatni.');
        $lines[] = str_repeat('-', 45);

        foreach ($packages as $package) {
            $group = $package->umrahGroupFlight;

            $lines[] = $package->package_no . ' | ' . $package->title;
            $lines[] = 'Group: ' . ($package->group_code ?: '-');
            $lines[] = 'Airline: ' . ($package->airline ?: '-');
            $lines[] = 'Route: ' . ($package->flight_route ?: ($group?->route ?: '-'));
            $lines[] = 'Departure: ' . ($group?->departure_date ? $group->departure_date->format('d M Y') : '-');
            $lines[] = 'Return: ' . ($group?->return_date ? $group->return_date->format('d M Y') : '-');
            $lines[] = 'Duration: ' . ($package->duration_nights ?: '-') . ' Night(s)';
            $lines[] = 'Makkah: ' . ($package->makkah_hotel ?: '-') . ' / ' . ($package->makkah_nights ?: '-') . ' Night(s)';
            $lines[] = 'Madinah: ' . ($package->madina_hotel ?: '-') . ' / ' . ($package->madina_nights ?: '-') . ' Night(s)';
            $lines[] = 'Prices: Sharing ' . ($package->sharing_price ? number_format((float) $package->sharing_price, 0) : 'N/A')
                . ' | Quad ' . ($package->quad_price ? number_format((float) $package->quad_price, 0) : 'N/A')
                . ' | Triple ' . ($package->triple_price ? number_format((float) $package->triple_price, 0) : 'N/A')
                . ' | Double ' . ($package->double_price ? number_format((float) $package->double_price, 0) : 'N/A');
            $lines[] = 'Seats: ' . $package->seats_left . ' / ' . $package->seats_available;
            $lines[] = str_repeat('-', 45);
        }

        return implode(PHP_EOL, $lines);
    }
}