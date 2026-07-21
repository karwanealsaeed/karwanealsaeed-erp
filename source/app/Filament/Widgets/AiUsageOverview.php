<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\AiUsageLog;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AiUsageOverview extends StatsOverviewWidget
{
    protected static bool $isLazy =
        false;

    public static function canView(): bool
    {
        return auth()->user()?->hasPermission(
            'ai_usage_logs_view',
        ) ?? false;
    }

    protected function getStats(): array
    {
        $todayQuery =
            AiUsageLog::query()
                ->whereDate(
                    'created_at',
                    today(),
                );

        $requestsToday =
            (clone $todayQuery)->count();

        $failedToday =
            (clone $todayQuery)
                ->where(
                    'status',
                    'failed',
                )
                ->count();

        $fallbacksToday =
            (clone $todayQuery)
                ->where(
                    'fallback_used',
                    true,
                )
                ->count();

        $averageLatency =
            (int) round(
                (float) (
                    (clone $todayQuery)->avg(
                        'latency_ms',
                    )
                    ?? 0
                ),
            );

        return [
            Stat::make(
                'Requests Today',
                number_format(
                    $requestsToday,
                ),
            )
                ->description(
                    'Privacy-safe usage records',
                ),

            Stat::make(
                'Fallbacks Today',
                number_format(
                    $fallbacksToday,
                ),
            )
                ->description(
                    'Local or safe fallback responses',
                ),

            Stat::make(
                'Failed Today',
                number_format(
                    $failedToday,
                ),
            )
                ->description(
                    'Requests returned through safe failure handling',
                ),

            Stat::make(
                'Average Latency',
                number_format(
                    $averageLatency,
                ).' ms',
            )
                ->description(
                    'Today average response time',
                ),
        ];
    }
}
