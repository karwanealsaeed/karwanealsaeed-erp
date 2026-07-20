<?php

namespace App\Services;

use App\Models\VisaApplication;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Throwable;

class PassportDashboardService
{
    public const EXPIRING_SOON_DAYS = 180;

    public const ACTION_STATUSES = [
        PassportValidityService::STATUS_EXPIRED,
        PassportValidityService::STATUS_RENEWAL_REQUIRED,
        PassportValidityService::STATUS_NOT_VALID_FOR_JOURNEY,
        PassportValidityService::STATUS_MISSING_EXPIRY,
    ];

    public function summary(): array
    {
        $base = $this->activeQuery();

        $statusCounts = (clone $base)
            ->selectRaw(
                'passport_validity_status, COUNT(*) as total',
            )
            ->groupBy(
                'passport_validity_status',
            )
            ->pluck(
                'total',
                'passport_validity_status',
            );

        $expired = (int) (
            $statusCounts[
                PassportValidityService::STATUS_EXPIRED
            ] ?? 0
        );

        $renewalRequired = (int) (
            $statusCounts[
                PassportValidityService
                    ::STATUS_RENEWAL_REQUIRED
            ] ?? 0
        );

        $journeyInvalid = (int) (
            $statusCounts[
                PassportValidityService
                    ::STATUS_NOT_VALID_FOR_JOURNEY
            ] ?? 0
        );

        $missingExpiry = (int) (
            $statusCounts[
                PassportValidityService
                    ::STATUS_MISSING_EXPIRY
            ] ?? 0
        );

        $missingTravelDate = (int) (
            $statusCounts[
                PassportValidityService
                    ::STATUS_MISSING_TRAVEL_DATE
            ] ?? 0
        );

        $valid = (int) (
            $statusCounts[
                PassportValidityService::STATUS_VALID
            ] ?? 0
        );

        $expiringSoon = $this->expiringSoonQuery(
            clone $base,
        )->count();

        return [
            'total' =>
                (clone $base)->count(),

            'action_required' =>
                $expired
                + $renewalRequired
                + $journeyInvalid
                + $missingExpiry
                + $expiringSoon,

            'expired' =>
                $expired,

            'renewal_required' =>
                $renewalRequired,

            'journey_invalid' =>
                $journeyInvalid,

            'missing_expiry' =>
                $missingExpiry,

            'missing_travel_date' =>
                $missingTravelDate,

            'expiring_soon' =>
                $expiringSoon,

            'valid' =>
                $valid,
        ];
    }

    public function actionRows(
        int $limit = 10,
    ): Collection {
        return $this->actionRequiredQuery(
            $this->activeQuery(),
        )
            ->with([
                'customer',
                'country',
                'visaType',
            ])
            ->orderByRaw(
                $this->priorityOrderSql(),
            )
            ->orderBy(
                'passport_expiry_date',
            )
            ->orderBy(
                'travel_date',
            )
            ->limit(
                max(
                    1,
                    min(100, $limit),
                ),
            )
            ->get();
    }

    public function reportRows(
        string $statusFilter =
            'Action Required',
        string $search = '',
        ?string $fromDate = null,
        ?string $toDate = null,
        int $limit = 250,
    ): Collection {
        $query = $this->activeQuery()
            ->with([
                'customer',
                'country',
                'visaType',
            ]);

        $this->applyStatusFilter(
            $query,
            $statusFilter,
        );

        $search = trim($search);

        if ($search !== '') {
            $query->where(
                function (
                    Builder $query,
                ) use ($search): void {
                    $query
                        ->where(
                            'application_no',
                            'like',
                            "%{$search}%",
                        )
                        ->orWhere(
                            'passport_no',
                            'like',
                            "%{$search}%",
                        )
                        ->orWhereHas(
                            'customer',
                            function (
                                Builder $query,
                            ) use ($search): void {
                                $query->where(
                                    'name',
                                    'like',
                                    "%{$search}%",
                                );
                            },
                        );
                },
            );
        }

        $from = $this->dateString(
            $fromDate,
        );

        $to = $this->dateString(
            $toDate,
        );

        if ($from) {
            $query->whereDate(
                'passport_expiry_date',
                '>=',
                $from,
            );
        }

        if ($to) {
            $query->whereDate(
                'passport_expiry_date',
                '<=',
                $to,
            );
        }

        return $query
            ->orderByRaw(
                $this->priorityOrderSql(),
            )
            ->orderBy(
                'passport_expiry_date',
            )
            ->orderBy(
                'travel_date',
            )
            ->limit(
                max(
                    1,
                    min(1000, $limit),
                ),
            )
            ->get();
    }

    public function statusOptions(): array
    {
        return [
            'Action Required' =>
                'Action Required',

            'All' =>
                'All Passport Records',

            PassportValidityService
                ::STATUS_EXPIRED =>
                    'Expired',

            PassportValidityService
                ::STATUS_RENEWAL_REQUIRED =>
                    'Renewal Required',

            PassportValidityService
                ::STATUS_NOT_VALID_FOR_JOURNEY =>
                    'Not Valid for Journey',

            PassportValidityService
                ::STATUS_MISSING_EXPIRY =>
                    'Missing Passport Expiry',

            'Passport Expiring Soon' =>
                'Passport Expiring Soon',

            PassportValidityService
                ::STATUS_MISSING_TRAVEL_DATE =>
                    'Missing Travel Date',

            PassportValidityService
                ::STATUS_VALID =>
                    'Valid',
        ];
    }

    public function rowAlertStatus(
        VisaApplication $application,
    ): string {
        if (
            in_array(
                $application
                    ->passport_validity_status,
                self::ACTION_STATUSES,
                true,
            )
        ) {
            return (string) $application
                ->passport_validity_status;
        }

        if (
            $application
                ->passport_validity_status
            === PassportValidityService
                ::STATUS_VALID
            && $this->isExpiringSoon(
                $application,
            )
        ) {
            return 'Passport Expiring Soon';
        }

        return $application
            ->passport_validity_status
            ?: 'Not Checked';
    }

    public function isExpiringSoon(
        VisaApplication $application,
    ): bool {
        if (
            ! $application
                ->passport_expiry_date
        ) {
            return false;
        }

        $today =
            CarbonImmutable::today();

        $expiry =
            CarbonImmutable::parse(
                $application
                    ->passport_expiry_date,
            )->startOfDay();

        return $expiry->gte($today)
            && $expiry->lte(
                $today->addDays(
                    self::EXPIRING_SOON_DAYS,
                ),
            );
    }

    private function activeQuery(): Builder
    {
        return VisaApplication::query()
            ->where(
                function (
                    Builder $query,
                ): void {
                    $query
                        ->whereNull('status')
                        ->orWhereNotIn(
                            'status',
                            [
                                'Rejected',
                                'Delivered',
                            ],
                        );
                },
            );
    }

    private function actionRequiredQuery(
        Builder $query,
    ): Builder {
        return $query->where(
            function (
                Builder $query,
            ): void {
                $query
                    ->whereIn(
                        'passport_validity_status',
                        self::ACTION_STATUSES,
                    )
                    ->orWhere(
                        function (
                            Builder $query,
                        ): void {
                            $this->expiringSoonQuery(
                                $query,
                            );
                        },
                    );
            },
        );
    }

    private function expiringSoonQuery(
        Builder $query,
    ): Builder {
        $today =
            CarbonImmutable::today();

        return $query
            ->where(
                'passport_validity_status',
                PassportValidityService
                    ::STATUS_VALID,
            )
            ->whereNotNull(
                'passport_expiry_date',
            )
            ->whereDate(
                'passport_expiry_date',
                '>=',
                $today->toDateString(),
            )
            ->whereDate(
                'passport_expiry_date',
                '<=',
                $today
                    ->addDays(
                        self::EXPIRING_SOON_DAYS,
                    )
                    ->toDateString(),
            );
    }

    private function applyStatusFilter(
        Builder $query,
        string $statusFilter,
    ): void {
        if (
            $statusFilter
            === 'Action Required'
        ) {
            $this->actionRequiredQuery(
                $query,
            );

            return;
        }

        if (
            $statusFilter
            === 'Passport Expiring Soon'
        ) {
            $this->expiringSoonQuery(
                $query,
            );

            return;
        }

        if ($statusFilter === 'All') {
            return;
        }

        if (
            array_key_exists(
                $statusFilter,
                $this->statusOptions(),
            )
        ) {
            $query->where(
                'passport_validity_status',
                $statusFilter,
            );

            return;
        }

        $this->actionRequiredQuery(
            $query,
        );
    }

    private function priorityOrderSql(): string
    {
        return <<<'SQL'
CASE
    WHEN passport_validity_status = 'Expired' THEN 1
    WHEN passport_validity_status = 'Not Valid for Journey' THEN 2
    WHEN passport_validity_status = 'Missing Passport Expiry' THEN 3
    WHEN passport_validity_status = 'Renewal Required' THEN 4
    WHEN passport_validity_status = 'Valid' THEN 5
    ELSE 6
END
SQL;
    }

    private function dateString(
        ?string $value,
    ): ?string {
        if (blank($value)) {
            return null;
        }

        try {
            return CarbonImmutable::parse(
                $value,
            )->toDateString();
        } catch (Throwable) {
            return null;
        }
    }
}
