<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\InsuranceProvider;
use App\Services\TravelInsuranceReportService;
use Filament\Pages\Page;

class TravelInsuranceReport extends Page
{
    protected string $view =
        'filament.pages.travel-insurance-report';

    protected static string | \BackedEnum | null $navigationIcon =
        'heroicon-o-chart-bar';

    protected static string | \UnitEnum | null $navigationGroup =
        'Reports';

    protected static ?string $navigationLabel =
        'Travel Insurance Report';

    protected static ?string $title =
        'Travel Insurance Report';

    protected static ?int $navigationSort =
        29;

    public static function canAccess(): bool
    {
        return auth()->user()
            ?->canViewReports()
            ?? false;
    }

    public function getReportData(): array
    {
        $service =
            app(
                TravelInsuranceReportService::class,
            );

        $filters =
            $service->normalizeFilters(
                request()->only(
                    [
                        'date_from',
                        'date_to',
                        'provider_id',
                        'status',
                        'approval_status',
                        'payment_status',
                        'search',
                    ],
                ),
            );

        $query =
            $service->query(
                $filters,
            );

        return [
            'filters' =>
                $filters,

            'summary' =>
                $service->summary(
                    clone $query,
                ),

            'policies' =>
                $query
                    ->orderByDesc(
                        'policy_start_date',
                    )
                    ->orderByDesc(
                        'id',
                    )
                    ->paginate(
                        25,
                        [
                            '*',
                        ],
                        'insurance_report_page',
                    )
                    ->withQueryString(),
        ];
    }

    public function providerOptions(): array
    {
        return InsuranceProvider::query()
            ->orderBy(
                'name',
            )
            ->get()
            ->mapWithKeys(
                function (
                    InsuranceProvider $provider,
                ): array {
                    return [
                        $provider->id =>
                            $provider->display_name
                            ?: $provider->name,
                    ];
                },
            )
            ->all();
    }

    public function policyStatusOptions(): array
    {
        return array_combine(
            TravelInsuranceReportService::POLICY_STATUSES,
            TravelInsuranceReportService::POLICY_STATUSES,
        );
    }

    public function approvalStatusOptions(): array
    {
        return array_combine(
            TravelInsuranceReportService::APPROVAL_STATUSES,
            TravelInsuranceReportService::APPROVAL_STATUSES,
        );
    }

    public function paymentStatusOptions(): array
    {
        return array_combine(
            TravelInsuranceReportService::PAYMENT_STATUSES,
            TravelInsuranceReportService::PAYMENT_STATUSES,
        );
    }

    public function exportUrl(
        string $format,
    ): string {
        $parameters = [
            'format' =>
                $format,
        ];

        foreach (
            request()->only(
                [
                    'date_from',
                    'date_to',
                    'provider_id',
                    'status',
                    'approval_status',
                    'payment_status',
                    'search',
                ],
            )
            as $key => $value
        ) {
            if (filled($value)) {
                $parameters[$key] =
                    $value;
            }
        }

        return route(
            'travel-insurance-report.export',
            $parameters,
        );
    }
}