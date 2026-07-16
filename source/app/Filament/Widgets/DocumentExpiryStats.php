<?php

namespace App\Filament\Widgets;

use App\Models\CustomerDocument;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DocumentExpiryStats extends StatsOverviewWidget
{
    protected static ?int $sort = 20;

    public static function canView(): bool
    {
        return auth()->user()?->hasPermission('reports.view') ?? false;
    }

    protected function getStats(): array
    {
        $today = now()->toDateString();

        $expired = CustomerDocument::query()
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', $today)
            ->count();

        $next30Days = CustomerDocument::query()
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', $today)
            ->whereDate('expiry_date', '<=', now()->addDays(30)->toDateString())
            ->count();

        $next90Days = CustomerDocument::query()
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', $today)
            ->whereDate('expiry_date', '<=', now()->addDays(90)->toDateString())
            ->count();

        return [
            Stat::make('Expired Documents', $expired)
                ->description('Already expired')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color($expired > 0 ? 'danger' : 'success'),

            Stat::make('Expiring in 30 Days', $next30Days)
                ->description('Need attention soon')
                ->descriptionIcon('heroicon-o-clock')
                ->color($next30Days > 0 ? 'warning' : 'success'),

            Stat::make('Expiring in 90 Days', $next90Days)
                ->description('Upcoming expiries')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color($next90Days > 0 ? 'info' : 'success'),
        ];
    }
}
