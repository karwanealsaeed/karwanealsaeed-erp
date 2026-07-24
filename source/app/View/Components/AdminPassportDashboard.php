<?php

namespace App\View\Components;

use App\Services\PassportDashboardService;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class AdminPassportDashboard extends Component
{
    public bool $canView = false;

    public array $stats = [];

    public Collection $rows;

    public function __construct()
    {
        $this->rows = collect();

        $user = auth()->user();

        if (! $user) {
            return;
        }

        $this->canView =
            (bool) $user->is_active
            && (
                $user->isAdmin()
                || $user->hasPermission('visa_applications_view')
            );

        if (! $this->canView) {
            return;
        }

        $service = app(
            PassportDashboardService::class,
        );

        $this->stats =
            $service->summary();

        $this->rows =
            $service->actionRows(10);
    }

    public function render(): View|Closure|string
    {
        return view(
            'components.admin-passport-dashboard',
        );
    }
}
