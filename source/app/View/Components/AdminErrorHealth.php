<?php

namespace App\View\Components;

use App\Models\SystemErrorIncident;
use App\Services\SystemErrorService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\Component;

class AdminErrorHealth extends Component
{
    public function shouldRender(): bool
    {
        $user =
            auth()->user();

        if (
            ! $user
            || ! (
                $user->isAdmin()
                || $user->hasPermission(
                    'settings.manage',
                )
            )
        ) {
            return false;
        }

        if (
            ! Schema::hasTable(
                'system_error_incidents',
            )
        ) {
            return false;
        }

        return SystemErrorIncident::query()
            ->unresolved()
            ->exists();
    }

    public function render(): View
    {
        $summary =
            app(
                SystemErrorService::class,
            )->summary();

        $incidents =
            SystemErrorIncident::query()
                ->unresolved()
                ->latest(
                    'last_seen_at',
                )
                ->limit(3)
                ->get();

        return view(
            'components.admin-error-health',
            [
                'summary' =>
                    $summary,

                'incidents' =>
                    $incidents,
            ],
        );
    }
}