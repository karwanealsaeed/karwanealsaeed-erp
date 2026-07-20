<?php

namespace App\Filament\Pages;

use App\Services\PassportDashboardService;
use Filament\Pages\Page;

class PassportValidityReport extends Page
{
    protected static string|\BackedEnum|null
        $navigationIcon =
            'heroicon-o-identification';

    protected static ?string
        $navigationLabel =
            'Passport Validity Report';

    protected static ?string
        $title =
            'Passport Validity Report';

    protected static string|\UnitEnum|null
        $navigationGroup =
            'Reports';

    protected static ?int
        $navigationSort = 8;

    protected string $view =
        'filament.pages.passport-validity-report';

    public string $statusFilter =
        'Action Required';

    public string $search = '';

    public ?string $fromDate = null;

    public ?string $toDate = null;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return in_array(
            $user->role,
            [
                'Admin',
                'Manager',
            ],
            true,
        )
        || $user->hasPermission(
            'visa_applications.view',
        )
        || $user->hasPermission(
            'reports.view',
        );
    }

    protected function getViewData(): array
    {
        $service = app(
            PassportDashboardService::class,
        );

        return [
            'stats' =>
                $service->summary(),

            'rows' =>
                $service->reportRows(
                    statusFilter:
                        $this->statusFilter,

                    search:
                        $this->search,

                    fromDate:
                        $this->fromDate,

                    toDate:
                        $this->toDate,
                ),

            'statusOptions' =>
                $service->statusOptions(),

            'passportService' =>
                $service,
        ];
    }
}
