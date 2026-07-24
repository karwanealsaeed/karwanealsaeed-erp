<?php

namespace App\Filament\CustomPages;

use App\Models\User;
use App\Support\ProposalRoleMapping;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Dashboard extends BaseDashboard
{
    protected string $view = 'filament.pages.dashboard';

    protected static string | \BackedEnum | null $navigationIcon =
        'heroicon-o-home';

    protected static ?string $navigationLabel =
        'Dashboard';

    protected static ?int $navigationSort = 1;

    private const PAYMENT_TABLES = [
        'payments',
        'flight_payments',
        'umrah_payments',
        'hotel_payments',
    ];

    public function getHeading(): string
    {
        return '';
    }

    public function getWidgets(): array
    {
        return [];
    }

    protected function getViewData(): array
    {
        $user = $this->currentUser();

        $showExecutiveFinance =
            $this->canViewExecutiveFinance($user);

        $showRecentBookings =
            $this->hasAnyPermission(
                $user,
                [
                    'visa_applications.view',
                    'flight_bookings.view',
                    'umrah_bookings.view',
                    'hotel_bookings.view',
                ],
            );

        $showRecentPayments =
            $showExecutiveFinance
            && $this->hasAnyPermission(
                $user,
                [
                    'visa_payments.view',
                    'flight_payments.view',
                    'umrah_payments.view',
                    'hotel_payments.view',
                ],
            );

        $stats = [];

        if (
            $this->hasPermission(
                $user,
                'customers.view',
            )
        ) {
            $stats[] = $this->countStat(
                table: 'customers',
                label: 'Total Customers',
                description: 'All customer records',
                icon: '👥',
                tone: 'green',
                url: url('/admin/customers'),
            );
        }

        if (
            $this->hasPermission(
                $user,
                'visa_applications.view',
            )
        ) {
            $stats[] = $this->countStat(
                table: 'visa_applications',
                label: 'Visa Applications',
                description: 'All visa application records',
                icon: '🛂',
                tone: 'gold',
                url: url('/admin/visa-applications'),
            );
        }

        if (
            $this->hasPermission(
                $user,
                'flight_bookings.view',
            )
        ) {
            $stats[] = $this->countStat(
                table: 'flight_bookings',
                label: 'Flight Bookings',
                description: 'All flight booking records',
                icon: '✈️',
                tone: 'green',
                url: url('/admin/flight-bookings'),
            );
        }

        if (
            $this->hasPermission(
                $user,
                'umrah_bookings.view',
            )
        ) {
            $stats[] = $this->countStat(
                table: 'umrah_bookings',
                label: 'Umrah Bookings',
                description: 'All Umrah booking records',
                icon: '🕋',
                tone: 'gold',
                url: url('/admin/umrah-bookings'),
            );
        }

        if (
            $this->hasPermission(
                $user,
                'hotel_bookings.view',
            )
        ) {
            $stats[] = $this->countStat(
                table: 'hotel_bookings',
                label: 'Hotel Bookings',
                description: 'All hotel booking records',
                icon: '🏨',
                tone: 'green',
                url: url('/admin/hotel-bookings'),
            );
        }

        $revenue = $this->sumMetric(
            self::PAYMENT_TABLES,
        );

        $todayCollection =
            $this->sumForDate(
                self::PAYMENT_TABLES,
                now('Asia/Karachi'),
            );

        if ($showExecutiveFinance) {
            $stats[] = [
                'label' => 'Total Sales',
                'value' => $this->money(
                    $revenue['total'],
                ),
                'description' =>
                    'Actual received payments',
                'icon' => '💳',
                'tone' => 'gold',
                'trend' => $this->trendLabel(
                    $revenue['current'],
                    $revenue['previous'],
                ),
                'url' =>
                    url('/admin/daily-finance-report'),
            ];

            $stats[] = [
                'label' => 'Today Collection',
                'value' => $this->money(
                    $todayCollection,
                ),
                'description' =>
                    'Payments received today',
                'icon' => '💰',
                'tone' => 'green',
                'trend' =>
                    $todayCollection > 0
                        ? 'Recorded today'
                        : 'No collection today',
                'url' =>
                    url('/admin/daily-finance-report'),
            ];

            if (
                $this->hasPermission(
                    $user,
                    'expenses.view',
                )
            ) {
                $expenses =
                    $this->sumMetric(
                        ['expenses'],
                    );

                $stats[] = [
                    'label' => 'Total Expenses',
                    'value' => $this->money(
                        $expenses['total'],
                    ),
                    'description' =>
                        'All recorded expenses',
                    'icon' => '💸',
                    'tone' => 'red',
                    'trend' =>
                        $this->trendLabel(
                            $expenses['current'],
                            $expenses['previous'],
                            inverse: true,
                        ),
                    'url' =>
                        url('/admin/expenses'),
                ];
            }
        }

        $chart = $this->chartData();

        $currentUserName =
            $user?->name ?: 'User';

        return [
            'stats' =>
                $stats,

            'currentUserName' =>
                $currentUserName,

            'currentUserRole' =>
                $this->dashboardRoleLabel($user),

            'currentUserInitial' =>
                strtoupper(
                    substr(
                        trim($currentUserName),
                        0,
                        1,
                    ),
                ),

            'showExecutiveFinance' =>
                $showExecutiveFinance,

            'showRecentBookings' =>
                $showRecentBookings,

            'showRecentPayments' =>
                $showRecentPayments,

            'totalRevenue' =>
                $this->money(
                    $revenue['total'],
                ),

            'revenueTrend' =>
                $this->trendLabel(
                    $revenue['current'],
                    $revenue['previous'],
                ),

            'chart' =>
                $chart,

            'recentBookings' =>
                $showRecentBookings
                    ? $this->recentBookings($user)
                    : collect(),

            'recentPayments' =>
                $showRecentPayments
                    ? $this->recentPayments($user)
                    : collect(),

            'currentDate' =>
                now('Asia/Karachi')
                    ->format('l, d M Y'),

            'currentTime' =>
                now('Asia/Karachi')
                    ->format('h:i A').' PKT',
        ];
    }

    private function currentUser(): ?User
    {
        $user = auth()->user();

        return $user instanceof User
            ? $user
            : null;
    }

    private function canViewExecutiveFinance(
        ?User $user,
    ): bool {
        if (! $user) {
            return false;
        }

        return $user->isAdmin()
            || $user->canViewReports();
    }

    private function dashboardRoleLabel(?User $user): string
    {
        if ($user === null) {
            return 'Staff';
        }

        $actualPermissions = collect($user->permissions ?? [])
            ->map(
                static fn (mixed $permission): string =>
                    str_replace(
                        '.',
                        '_',
                        trim((string) $permission),
                    ),
            )
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        foreach (
            ProposalRoleMapping::mappings()
            as $label => $mapping
        ) {
            if (
                ($mapping['identity'] ?? null) !== 'staff'
                || ($mapping['base_role'] ?? null) !== $user->role
            ) {
                continue;
            }

            $mappedPermissions = collect(
                $mapping['permissions'] ?? [],
            )
                ->map(
                    static fn (mixed $permission): string =>
                        str_replace(
                            '.',
                            '_',
                            trim((string) $permission),
                        ),
                )
                ->filter()
                ->unique()
                ->sort()
                ->values()
                ->all();

            if ($actualPermissions === $mappedPermissions) {
                return (string) $label;
            }
        }

        return $user->role ?: 'Staff';
    }
    private function hasPermission(
        ?User $user,
        string $permission,
    ): bool {
        return $user?->hasPermission(
            $permission,
        ) ?? false;
    }

    private function hasAnyPermission(
        ?User $user,
        array $permissions,
    ): bool {
        return $user?->hasAnyPermission(
            $permissions,
        ) ?? false;
    }

    private function countStat(
        string $table,
        string $label,
        string $description,
        string $icon,
        string $tone,
        string $url,
    ): array {
        $metric =
            $this->countMetric($table);

        return [
            'label' =>
                $label,

            'value' =>
                number_format(
                    $metric['total'],
                ),

            'description' =>
                $description,

            'icon' =>
                $icon,

            'tone' =>
                $tone,

            'trend' =>
                $this->trendLabel(
                    $metric['current'],
                    $metric['previous'],
                ),

            'url' =>
                $url,
        ];
    }

    private function countMetric(
        string $table,
    ): array {
        if (! Schema::hasTable($table)) {
            return [
                'total' => 0,
                'current' => 0,
                'previous' => 0,
            ];
        }

        $dateColumn =
            $this->dateColumn($table);

        $total =
            (int) DB::table($table)
                ->count();

        if (! $dateColumn) {
            return [
                'total' => $total,
                'current' => 0,
                'previous' => 0,
            ];
        }

        [$currentStart, $currentEnd] =
            $this->currentMonthRange();

        [$previousStart, $previousEnd] =
            $this->previousMonthRange();

        return [
            'total' =>
                $total,

            'current' =>
                (int) DB::table($table)
                    ->whereBetween(
                        $dateColumn,
                        [
                            $currentStart,
                            $currentEnd,
                        ],
                    )
                    ->count(),

            'previous' =>
                (int) DB::table($table)
                    ->whereBetween(
                        $dateColumn,
                        [
                            $previousStart,
                            $previousEnd,
                        ],
                    )
                    ->count(),
        ];
    }

    private function sumMetric(
        array $tables,
    ): array {
        $metric = [
            'total' => 0.0,
            'current' => 0.0,
            'previous' => 0.0,
        ];

        [$currentStart, $currentEnd] =
            $this->currentMonthRange();

        [$previousStart, $previousEnd] =
            $this->previousMonthRange();

        foreach ($tables as $table) {
            $metric['total'] +=
                $this->sumTable($table);

            $metric['current'] +=
                $this->sumTableForPeriod(
                    $table,
                    $currentStart,
                    $currentEnd,
                );

            $metric['previous'] +=
                $this->sumTableForPeriod(
                    $table,
                    $previousStart,
                    $previousEnd,
                );
        }

        return $metric;
    }

    private function sumTable(
        string $table,
    ): float {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        $column =
            $this->amountColumn($table);

        if (! $column) {
            return 0;
        }

        return (float) DB::table($table)
            ->sum($column);
    }

    private function sumTableForPeriod(
        string $table,
        CarbonInterface $start,
        CarbonInterface $end,
    ): float {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        $amountColumn =
            $this->amountColumn($table);

        $dateColumn =
            $this->dateColumn($table);

        if (! $amountColumn || ! $dateColumn) {
            return 0;
        }

        return (float) DB::table($table)
            ->whereBetween(
                $dateColumn,
                [
                    $start,
                    $end,
                ],
            )
            ->sum($amountColumn);
    }

    private function sumForDate(
        array $tables,
        CarbonInterface $date,
    ): float {
        $total = 0;

        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $amountColumn =
                $this->amountColumn($table);

            $dateColumn =
                $this->dateColumn($table);

            if (! $amountColumn || ! $dateColumn) {
                continue;
            }

            $total +=
                (float) DB::table($table)
                    ->whereDate(
                        $dateColumn,
                        $date->toDateString(),
                    )
                    ->sum($amountColumn);
        }

        return $total;
    }

    private function amountColumn(
        string $table,
    ): ?string {
        return $this->firstExistingColumn(
            $table,
            [
                'amount',
                'paid_amount',
                'total_amount',
                'package_amount',
                'fee',
                'total_fee',
            ],
        );
    }

    private function dateColumn(
        string $table,
    ): ?string {
        return $this->firstExistingColumn(
            $table,
            [
                'payment_date',
                'expense_date',
                'created_at',
            ],
        );
    }

    private function firstExistingColumn(
        string $table,
        array $columns,
    ): ?string {
        if (! Schema::hasTable($table)) {
            return null;
        }

        foreach ($columns as $column) {
            if (
                Schema::hasColumn(
                    $table,
                    $column,
                )
            ) {
                return $column;
            }
        }

        return null;
    }

    private function currentMonthRange(): array
    {
        $now =
            now('Asia/Karachi');

        return [
            $now->copy()
                ->startOfMonth()
                ->startOfDay(),

            $now->copy()
                ->endOfMonth()
                ->endOfDay(),
        ];
    }

    private function previousMonthRange(): array
    {
        $previous =
            now('Asia/Karachi')
                ->subMonthNoOverflow();

        return [
            $previous->copy()
                ->startOfMonth()
                ->startOfDay(),

            $previous->copy()
                ->endOfMonth()
                ->endOfDay(),
        ];
    }

    private function trendLabel(
        float|int $current,
        float|int $previous,
        bool $inverse = false,
    ): string {
        if ($previous <= 0 && $current <= 0) {
            return 'No activity this month';
        }

        if ($previous <= 0) {
            return 'New activity this month';
        }

        $change =
            (($current - $previous) / $previous)
            * 100;

        if (abs($change) < 0.05) {
            return 'No change vs last month';
        }

        $isPositive =
            $change > 0;

        $arrow =
            $isPositive ? '↑' : '↓';

        if ($inverse) {
            $arrow =
                $isPositive ? '↑' : '↓';
        }

        return sprintf(
            '%s %s%% vs last month',
            $arrow,
            number_format(
                abs($change),
                1,
            ),
        );
    }

    private function money(
        float $amount,
    ): string {
        return 'PKR '
            .number_format(
                $amount,
                2,
            );
    }

    private function chartData(): array
    {
        $values = [];
        $labels = [];

        for ($i = 6; $i >= 0; $i--) {
            $date =
                now('Asia/Karachi')
                    ->subDays($i);

            $values[] =
                $this->sumForDate(
                    self::PAYMENT_TABLES,
                    $date,
                );

            $labels[] =
                $date->format('d M');
        }

        $max =
            max(
                max($values),
                1,
            );

        $width = 560;
        $height = 190;
        $padding = 18;

        $usableWidth =
            $width - ($padding * 2);

        $usableHeight =
            $height - ($padding * 2);

        $points = [];

        foreach (
            $values
            as $index => $value
        ) {
            $x =
                $padding
                + (
                    (
                        $usableWidth
                        / max(
                            count($values) - 1,
                            1,
                        )
                    )
                    * $index
                );

            $y =
                $height
                - $padding
                - (
                    ($value / $max)
                    * $usableHeight
                );

            $points[] = [
                'x' => round($x, 2),
                'y' => round($y, 2),
                'value' => $value,
            ];
        }

        $polyline =
            collect($points)
                ->map(
                    fn (array $point): string =>
                        $point['x']
                        .','
                        .$point['y'],
                )
                ->implode(' ');

        $area =
            'M '
            .$points[0]['x']
            .' '
            .$points[0]['y']
            .' ';

        foreach ($points as $point) {
            $area .=
                'L '
                .$point['x']
                .' '
                .$point['y']
                .' ';
        }

        $area .=
            'L '
            .$points[
                count($points) - 1
            ]['x']
            .' '
            .($height - $padding)
            .' ';

        $area .=
            'L '
            .$points[0]['x']
            .' '
            .($height - $padding)
            .' Z';

        return [
            'width' =>
                $width,

            'height' =>
                $height,

            'points' =>
                $points,

            'polyline' =>
                $polyline,

            'area' =>
                $area,

            'labels' =>
                $labels,

            'values' =>
                $values,

            'has_data' =>
                array_sum($values) > 0,
        ];
    }

    private function recentBookings(
        ?User $user,
    ): Collection {
        $configs = [
            [
                'table' => 'visa_applications',
                'permission' =>
                    'visa_applications.view',
                'type' => 'Visa',
                'icon' => '🛂',
            ],
            [
                'table' => 'flight_bookings',
                'permission' =>
                    'flight_bookings.view',
                'type' => 'Flight',
                'icon' => '✈️',
            ],
            [
                'table' => 'umrah_bookings',
                'permission' =>
                    'umrah_bookings.view',
                'type' => 'Umrah',
                'icon' => '🕋',
            ],
            [
                'table' => 'hotel_bookings',
                'permission' =>
                    'hotel_bookings.view',
                'type' => 'Hotel',
                'icon' => '🏨',
            ],
        ];

        $rows = collect();

        foreach ($configs as $config) {
            if (
                ! $this->hasPermission(
                    $user,
                    $config['permission'],
                )
            ) {
                continue;
            }

            $table =
                $config['table'];

            if (! Schema::hasTable($table)) {
                continue;
            }

            $orderColumn =
                Schema::hasColumn(
                    $table,
                    'created_at',
                )
                    ? 'created_at'
                    : 'id';

            DB::table($table)
                ->orderByDesc($orderColumn)
                ->limit(5)
                ->get()
                ->each(
                    function (
                        object $record,
                    ) use (
                        $rows,
                        $config,
                    ): void {
                        $rows->push([
                            'id' =>
                                $this->recordValue(
                                    $record,
                                    [
                                        'booking_no',
                                        'application_no',
                                        'invoice_no',
                                    ],
                                    strtoupper(
                                        substr(
                                            $config['type'],
                                            0,
                                            2,
                                        ),
                                    )
                                    .'-'
                                    .str_pad(
                                        (string) (
                                            $record->id
                                            ?? 0
                                        ),
                                        5,
                                        '0',
                                        STR_PAD_LEFT,
                                    ),
                                ),

                            'type' =>
                                $config['type'],

                            'icon' =>
                                $config['icon'],

                            'customer' =>
                                $this->customerName(
                                    $record->customer_id
                                    ?? null,
                                ),

                            'amount' =>
                                $this->money(
                                    $this->recordAmount(
                                        $record,
                                    ),
                                ),

                            'status' =>
                                $this->recordValue(
                                    $record,
                                    ['status'],
                                    'Pending',
                                ),

                            'date' =>
                                $this->recordDate(
                                    $record->created_at
                                    ?? null,
                                ),

                            'sort_date' =>
                                $record->created_at
                                ?? null,
                        ]);
                    },
                );
        }

        return $rows
            ->sortByDesc('sort_date')
            ->take(5)
            ->values();
    }

    private function recentPayments(
        ?User $user,
    ): Collection {
        $configs = [
            [
                'table' => 'payments',
                'permission' =>
                    'visa_payments.view',
                'prefix' => 'VP',
                'service' => 'Visa',
                'parent_table' =>
                    'visa_applications',
                'parent_key' =>
                    'visa_application_id',
            ],
            [
                'table' => 'flight_payments',
                'permission' =>
                    'flight_payments.view',
                'prefix' => 'FP',
                'service' => 'Flight',
                'parent_table' =>
                    'flight_bookings',
                'parent_key' =>
                    'flight_booking_id',
            ],
            [
                'table' => 'umrah_payments',
                'permission' =>
                    'umrah_payments.view',
                'prefix' => 'UP',
                'service' => 'Umrah',
                'parent_table' =>
                    'umrah_bookings',
                'parent_key' =>
                    'umrah_booking_id',
            ],
            [
                'table' => 'hotel_payments',
                'permission' =>
                    'hotel_payments.view',
                'prefix' => 'HP',
                'service' => 'Hotel',
                'parent_table' =>
                    'hotel_bookings',
                'parent_key' =>
                    'hotel_booking_id',
            ],
        ];

        $rows = collect();

        foreach ($configs as $config) {
            if (
                ! $this->hasPermission(
                    $user,
                    $config['permission'],
                )
            ) {
                continue;
            }

            $table =
                $config['table'];

            if (! Schema::hasTable($table)) {
                continue;
            }

            $orderColumn =
                $this->dateColumn($table)
                ?: 'id';

            DB::table($table)
                ->orderByDesc($orderColumn)
                ->limit(5)
                ->get()
                ->each(
                    function (
                        object $record,
                    ) use (
                        $rows,
                        $config,
                        $orderColumn,
                    ): void {
                        $rows->push([
                            'id' =>
                                $config['prefix']
                                .'-'
                                .str_pad(
                                    (string) (
                                        $record->id
                                        ?? 0
                                    ),
                                    5,
                                    '0',
                                    STR_PAD_LEFT,
                                ),

                            'service' =>
                                $config['service'],

                            'customer' =>
                                $this->paymentCustomerName(
                                    $record,
                                    $config,
                                ),

                            'method' =>
                                $this->recordValue(
                                    $record,
                                    [
                                        'payment_method',
                                        'method',
                                    ],
                                    'Cash',
                                ),

                            'amount' =>
                                $this->money(
                                    $this->recordAmount(
                                        $record,
                                    ),
                                ),

                            'date' =>
                                $this->recordDate(
                                    $record->{$orderColumn}
                                    ?? null,
                                ),

                            'status' =>
                                'Received',

                            'sort_date' =>
                                $record->{$orderColumn}
                                ?? null,
                        ]);
                    },
                );
        }

        return $rows
            ->sortByDesc('sort_date')
            ->take(5)
            ->values();
    }

    private function paymentCustomerName(
        object $record,
        array $config,
    ): string {
        $parentId =
            $record->{$config['parent_key']}
            ?? null;

        if (
            ! $parentId
            || ! Schema::hasTable(
                $config['parent_table'],
            )
        ) {
            return '-';
        }

        $customerId =
            DB::table(
                $config['parent_table'],
            )
                ->where('id', $parentId)
                ->value('customer_id');

        return $this->customerName(
            $customerId,
        );
    }

    private function customerName(
        mixed $customerId,
    ): string {
        if (
            ! $customerId
            || ! Schema::hasTable('customers')
        ) {
            return '-';
        }

        return (string) (
            DB::table('customers')
                ->where('id', $customerId)
                ->value('name')
            ?: '-'
        );
    }

    private function recordValue(
        object $record,
        array $columns,
        string $fallback = '-',
    ): string {
        foreach ($columns as $column) {
            if (
                property_exists(
                    $record,
                    $column,
                )
                && filled(
                    $record->{$column},
                )
            ) {
                return (string)
                    $record->{$column};
            }
        }

        return $fallback;
    }

    private function recordAmount(
        object $record,
    ): float {
        foreach (
            [
                'amount',
                'paid_amount',
                'total_amount',
                'package_amount',
                'fee',
                'total_fee',
            ]
            as $column
        ) {
            if (
                property_exists(
                    $record,
                    $column,
                )
                && is_numeric(
                    $record->{$column},
                )
            ) {
                return (float)
                    $record->{$column};
            }
        }

        return 0;
    }

    private function recordDate(
        mixed $date,
    ): string {
        if (! $date) {
            return '-';
        }

        return Carbon::parse($date)
            ->timezone('Asia/Karachi')
            ->format('d M Y');
    }
}