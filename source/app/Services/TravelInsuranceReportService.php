<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\TravelInsurancePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TravelInsuranceReportService
{
    public const POLICY_STATUSES = [
        'Draft',
        'Active',
        'Expired',
        'Cancelled',
        'Completed',
    ];

    public const APPROVAL_STATUSES = [
        'Pending',
        'Approved',
        'Rejected',
    ];

    public const PAYMENT_STATUSES = [
        'Unpaid',
        'Partially Paid',
        'Paid',
    ];

    public function normalizeFilters(
        array $filters,
    ): array {
        $dateFrom =
            $this->dateValue(
                $filters['date_from']
                ?? null,
            );

        $dateTo =
            $this->dateValue(
                $filters['date_to']
                ?? null,
            );

        if (
            $dateFrom
            && $dateTo
            && $dateFrom > $dateTo
        ) {
            [
                $dateFrom,
                $dateTo,
            ] = [
                $dateTo,
                $dateFrom,
            ];
        }

        $providerId =
            $filters['provider_id']
            ?? null;

        $providerId =
            is_numeric(
                $providerId,
            )
                ? max(
                    1,
                    (int) $providerId,
                )
                : null;

        $search =
            trim(
                (string) (
                    $filters['search']
                    ?? ''
                ),
            );

        $search =
            mb_substr(
                $search,
                0,
                100,
            );

        return [
            'date_from' =>
                $dateFrom,

            'date_to' =>
                $dateTo,

            'provider_id' =>
                $providerId,

            'status' =>
                $this->allowedValue(
                    $filters['status']
                    ?? null,
                    self::POLICY_STATUSES,
                ),

            'approval_status' =>
                $this->allowedValue(
                    $filters['approval_status']
                    ?? null,
                    self::APPROVAL_STATUSES,
                ),

            'payment_status' =>
                $this->allowedValue(
                    $filters['payment_status']
                    ?? null,
                    self::PAYMENT_STATUSES,
                ),

            'search' =>
                $search !== ''
                    ? $search
                    : null,
        ];
    }

    public function query(
        array $filters,
    ): Builder {
        $filters =
            $this->normalizeFilters(
                $filters,
            );

        $query =
            TravelInsurancePolicy::query()
                ->with(
                    [
                        'customer:id,name,mobile,email',
                        'provider:id,name,company_name',
                        'invoice:id,invoice_no,status',
                    ],
                );

        if ($filters['date_from']) {
            $query->whereDate(
                'policy_start_date',
                '>=',
                $filters['date_from'],
            );
        }

        if ($filters['date_to']) {
            $query->whereDate(
                'policy_start_date',
                '<=',
                $filters['date_to'],
            );
        }

        if ($filters['provider_id']) {
            $query->where(
                'insurance_provider_id',
                $filters['provider_id'],
            );
        }

        if ($filters['status']) {
            $query->where(
                'status',
                $filters['status'],
            );
        }

        if ($filters['approval_status']) {
            $query->where(
                'approval_status',
                $filters['approval_status'],
            );
        }

        if ($filters['payment_status']) {
            $query->where(
                'payment_status',
                $filters['payment_status'],
            );
        }

        if ($filters['search']) {
            $search =
                $filters['search'];

            $query->where(
                function (
                    Builder $query,
                ) use (
                    $search,
                ): void {
                    $query
                        ->where(
                            'policy_no',
                            'like',
                            "%{$search}%",
                        )
                        ->orWhere(
                            'traveller_name',
                            'like',
                            "%{$search}%",
                        )
                        ->orWhere(
                            'passport_no',
                            'like',
                            "%{$search}%",
                        )
                        ->orWhere(
                            'destination_country',
                            'like',
                            "%{$search}%",
                        )
                        ->orWhereHas(
                            'customer',
                            function (
                                Builder $query,
                            ) use (
                                $search,
                            ): void {
                                $query
                                    ->where(
                                        'name',
                                        'like',
                                        "%{$search}%",
                                    )
                                    ->orWhere(
                                        'mobile',
                                        'like',
                                        "%{$search}%",
                                    )
                                    ->orWhere(
                                        'email',
                                        'like',
                                        "%{$search}%",
                                    );
                            },
                        )
                        ->orWhereHas(
                            'provider',
                            function (
                                Builder $query,
                            ) use (
                                $search,
                            ): void {
                                $query
                                    ->where(
                                        'name',
                                        'like',
                                        "%{$search}%",
                                    )
                                    ->orWhere(
                                        'company_name',
                                        'like',
                                        "%{$search}%",
                                    );
                            },
                        );
                },
            );
        }

        return $query;
    }

    public function summary(
        Builder $query,
    ): array {
        return [
            'policy_count' =>
                (clone $query)
                    ->count(),

            'approved_count' =>
                (clone $query)
                    ->where(
                        'approval_status',
                        'Approved',
                    )
                    ->count(),

            'active_count' =>
                (clone $query)
                    ->where(
                        'status',
                        'Active',
                    )
                    ->count(),

            'expiring_soon_count' =>
                (clone $query)
                    ->whereDate(
                        'policy_end_date',
                        '>=',
                        today(),
                    )
                    ->whereDate(
                        'policy_end_date',
                        '<=',
                        today()->addDays(
                            30,
                        ),
                    )
                    ->count(),

            'premium_amount' =>
                round(
                    (float) (
                        clone $query
                    )->sum(
                        'premium_amount',
                    ),
                    2,
                ),

            'supplier_cost' =>
                round(
                    (float) (
                        clone $query
                    )->sum(
                        'supplier_cost',
                    ),
                    2,
                ),

            'total_amount' =>
                round(
                    (float) (
                        clone $query
                    )->sum(
                        'total_amount',
                    ),
                    2,
                ),

            'paid_amount' =>
                round(
                    (float) (
                        clone $query
                    )->sum(
                        'paid_amount',
                    ),
                    2,
                ),

            'balance_amount' =>
                round(
                    (float) (
                        clone $query
                    )->sum(
                        'balance_amount',
                    ),
                    2,
                ),

            'profit_amount' =>
                round(
                    (float) (
                        clone $query
                    )->sum(
                        'profit_amount',
                    ),
                    2,
                ),
        ];
    }

    public function rows(
        Builder $query,
        int $limit,
    ): Collection {
        return $query
            ->orderByDesc(
                'policy_start_date',
            )
            ->orderByDesc(
                'id',
            )
            ->limit(
                max(
                    1,
                    $limit,
                ),
            )
            ->get();
    }

    public function spreadsheetHeadings(): array
    {
        return [
            'Policy No',
            'Customer',
            'Mobile',
            'Traveller',
            'Passport No',
            'Provider',
            'Destination',
            'Policy Start',
            'Policy End',
            'Status',
            'Approval',
            'Payment Status',
            'Currency',
            'Premium',
            'Supplier Cost',
            'Discount',
            'Tax',
            'Total',
            'Paid',
            'Balance',
            'Profit',
            'Invoice No',
            'Invoice Status',
        ];
    }

    public function spreadsheetRow(
        TravelInsurancePolicy $policy,
    ): array {
        return [
            $this->spreadsheetText(
                $policy->policy_no,
            ),

            $this->spreadsheetText(
                $policy->customer?->name,
            ),

            $this->spreadsheetText(
                $policy->customer?->mobile,
            ),

            $this->spreadsheetText(
                $policy->traveller_name,
            ),

            $this->spreadsheetText(
                $policy->passport_no,
            ),

            $this->spreadsheetText(
                $policy->provider?->display_name
                ?? $policy->provider?->name,
            ),

            $this->spreadsheetText(
                $policy->destination_country,
            ),

            $policy->policy_start_date
                ?->format(
                    'Y-m-d',
                )
                ?? '',

            $policy->policy_end_date
                ?->format(
                    'Y-m-d',
                )
                ?? '',

            $this->spreadsheetText(
                $policy->status,
            ),

            $this->spreadsheetText(
                $policy->approval_status,
            ),

            $this->spreadsheetText(
                $policy->payment_status,
            ),

            $this->spreadsheetText(
                $policy->currency,
            ),

            number_format(
                (float) $policy->premium_amount,
                2,
                '.',
                '',
            ),

            number_format(
                (float) $policy->supplier_cost,
                2,
                '.',
                '',
            ),

            number_format(
                (float) $policy->discount,
                2,
                '.',
                '',
            ),

            number_format(
                (float) $policy->tax,
                2,
                '.',
                '',
            ),

            number_format(
                (float) $policy->total_amount,
                2,
                '.',
                '',
            ),

            number_format(
                (float) $policy->paid_amount,
                2,
                '.',
                '',
            ),

            number_format(
                (float) $policy->balance_amount,
                2,
                '.',
                '',
            ),

            number_format(
                (float) $policy->profit_amount,
                2,
                '.',
                '',
            ),

            $this->spreadsheetText(
                $policy->invoice?->invoice_no,
            ),

            $this->spreadsheetText(
                $policy->invoice?->status,
            ),
        ];
    }

    private function spreadsheetText(
        mixed $value,
    ): string {
        $value =
            trim(
                (string) $value,
            );

        if (
            $value !== ''
            && preg_match(
                '/^[=+\-@\t\r]/u',
                $value,
            ) === 1
        ) {
            return "'{$value}";
        }

        return $value;
    }

    private function dateValue(
        mixed $value,
    ): ?string {
        $value =
            trim(
                (string) $value,
            );

        if (
            preg_match(
                '/^\d{4}-\d{2}-\d{2}$/',
                $value,
            ) !== 1
        ) {
            return null;
        }

        [
            $year,
            $month,
            $day,
        ] = array_map(
            'intval',
            explode(
                '-',
                $value,
            ),
        );

        if (
            ! checkdate(
                $month,
                $day,
                $year,
            )
        ) {
            return null;
        }

        return $value;
    }

    private function allowedValue(
        mixed $value,
        array $allowed,
    ): ?string {
        $value =
            trim(
                (string) $value,
            );

        return in_array(
            $value,
            $allowed,
            true,
        )
            ? $value
            : null;
    }
}