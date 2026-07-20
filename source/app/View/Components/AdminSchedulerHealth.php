<?php

namespace App\View\Components;

use App\Services\SchedulerHealthService;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AdminSchedulerHealth extends Component
{
    public function shouldRender(): bool
    {
        $user =
            auth()->user();

        if (
            ! $user
            || ! $user->hasPermission(
                'settings.manage',
            )
        ) {
            return false;
        }

        return ! app(
            SchedulerHealthService::class,
        )->status()['healthy'];
    }

    public function render(): View
    {
        $service =
            app(
                SchedulerHealthService::class,
            );

        $status =
            $service->status();

        $service->alertIfStale();

        return view(
            'components.admin-scheduler-health',
            [
                'status' =>
                    $status,
            ],
        );
    }
}