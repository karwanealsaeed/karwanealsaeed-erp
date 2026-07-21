<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\TravelInsurancePolicy;
use App\Models\User;
use Carbon\CarbonInterface;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SendTravelInsuranceExpiryAlerts extends Command
{
    private const WARNING_DAYS =
        30;

    private const CRITICAL_DAYS =
        7;

    protected $signature =
        'travel-insurance:send-expiry-alerts
        {--dry-run : Show actions without sending or updating records}
        {--force : Send again even when the current fingerprint was already processed}';

    protected $description =
        'Send Travel Insurance policy expiry alerts to authorized active staff.';

    public function handle(): int
    {
        $today =
            now(
                'Asia/Karachi',
            )->startOfDay();

        $recipients =
            $this->recipients();

        $alerts =
            0;

        $notifications =
            0;

        $duplicates =
            0;

        $resolved =
            0;

        $withoutRecipients =
            0;

        TravelInsurancePolicy::query()
            ->with(
                [
                    'customer:id,name,mobile,email',
                    'provider:id,name,company_name',
                ],
            )
            ->where(
                function (
                    Builder $query,
                ) use (
                    $today,
                ): void {
                    $query
                        ->whereNotNull(
                            'expiry_alert_fingerprint',
                        )
                        ->orWhereNotNull(
                            'expiry_alert_sent_at',
                        )
                        ->orWhere(
                            function (
                                Builder $query,
                            ) use (
                                $today,
                            ): void {
                                $query
                                    ->where(
                                        'approval_status',
                                        'Approved',
                                    )
                                    ->whereIn(
                                        'status',
                                        [
                                            'Active',
                                            'Expired',
                                        ],
                                    )
                                    ->where(
                                        function (
                                            Builder $query,
                                        ) use (
                                            $today,
                                        ): void {
                                            $query
                                                ->whereNull(
                                                    'policy_end_date',
                                                )
                                                ->orWhereDate(
                                                    'policy_end_date',
                                                    '<=',
                                                    $today
                                                        ->copy()
                                                        ->addDays(
                                                            self::WARNING_DAYS,
                                                        )
                                                        ->toDateString(),
                                                );
                                        },
                                    );
                            },
                        );
                },
            )
            ->orderBy(
                'id',
            )
            ->chunkById(
                200,
                function (
                    Collection $policies,
                ) use (
                    $today,
                    $recipients,
                    &$alerts,
                    &$notifications,
                    &$duplicates,
                    &$resolved,
                    &$withoutRecipients,
                ): void {
                    foreach ($policies as $policy) {
                        $context =
                            $this->alertContext(
                                $policy,
                                $today,
                            );

                        if ($context === null) {
                            $hadAlert =
                                filled(
                                    $policy
                                        ->expiry_alert_fingerprint,
                                )
                                || filled(
                                    $policy
                                        ->expiry_alert_sent_at,
                                );

                            if (
                                $hadAlert
                                && ! $this->option(
                                    'dry-run',
                                )
                            ) {
                                $policy->forceFill(
                                    [
                                        'expiry_alert_fingerprint' =>
                                            null,

                                        'expiry_alert_sent_at' =>
                                            null,
                                    ],
                                )->saveQuietly();

                                $resolved++;
                            }

                            continue;
                        }

                        $fingerprint =
                            $this->fingerprint(
                                $policy,
                                $context,
                            );

                        $alreadySent =
                            hash_equals(
                                (string) (
                                    $policy
                                        ->expiry_alert_fingerprint
                                    ?? ''
                                ),
                                $fingerprint,
                            );

                        if (
                            $alreadySent
                            && ! $this->option(
                                'force',
                            )
                        ) {
                            $duplicates++;

                            continue;
                        }

                        $alerts++;

                        if (
                            $this->option(
                                'dry-run',
                            )
                        ) {
                            if ($recipients->isEmpty()) {
                                $this->line(
                                    'Would process '
                                    .$context['type']
                                    .': '
                                    .$this->policyLabel(
                                        $policy,
                                    )
                                    .' — no authorized recipient available.',
                                );

                                continue;
                            }

                            foreach ($recipients as $recipient) {
                                $this->line(
                                    "Would notify {$recipient->name}: "
                                    .$context['type']
                                    .' — '
                                    .$this->policyLabel(
                                        $policy,
                                    ),
                                );
                            }

                            continue;
                        }

                        if ($recipients->isEmpty()) {
                            $withoutRecipients++;

                            $this->warn(
                                'No authorized active recipient for '
                                .$context['type']
                                .': '
                                .$this->policyLabel(
                                    $policy,
                                ),
                            );

                            continue;
                        }

                        foreach ($recipients as $recipient) {
                            $recipient->notifyNow(
                                Notification::make()
                                    ->title(
                                        'Travel Insurance Alert: '
                                        .$context['type'],
                                    )
                                    ->body(
                                        $this->notificationBody(
                                            $policy,
                                            $context,
                                        ),
                                    )
                                    ->icon(
                                        'heroicon-o-shield-exclamation',
                                    )
                                    ->color(
                                        $context['color'],
                                    )
                                    ->actions(
                                        [
                                            Action::make(
                                                'view',
                                            )
                                                ->label(
                                                    'View Insurance Policy',
                                                )
                                                ->button()
                                                ->url(
                                                    route(
                                                        'filament.admin.resources.'
                                                        .'travel-insurance-policies.view',
                                                        [
                                                            'record' =>
                                                                $policy,
                                                        ],
                                                    ),
                                                )
                                                ->markAsRead(),
                                        ],
                                    )
                                    ->toDatabase(),
                            );

                            $notifications++;
                        }

                        $policy->forceFill(
                            [
                                'expiry_alert_fingerprint' =>
                                    $fingerprint,

                                'expiry_alert_sent_at' =>
                                    now(
                                        'Asia/Karachi',
                                    ),
                            ],
                        )->saveQuietly();

                        $this->info(
                            $context['type']
                            .': '
                            .$this->policyLabel(
                                $policy,
                            )
                            ." — {$recipients->count()} recipients.",
                        );
                    }
                },
            );

        if (
            $this->option(
                'dry-run',
            )
        ) {
            $this->info(
                "Dry run completed: {$alerts} actionable alerts, "
                ."{$duplicates} duplicates, "
                ."{$resolved} resolved alerts detected.",
            );

            return self::SUCCESS;
        }

        $this->info(
            "Completed: {$alerts} alerts, "
            ."{$notifications} notifications, "
            ."{$duplicates} duplicates skipped, "
            ."{$resolved} resolved alerts reset, "
            ."{$withoutRecipients} alerts awaiting recipients.",
        );

        return self::SUCCESS;
    }

    private function recipients(): Collection
    {
        return User::query()
            ->where(
                'is_active',
                true,
            )
            ->get()
            ->filter(
                function (
                    User $user,
                ): bool {
                    return in_array(
                        $user->role,
                        [
                            'Admin',
                            'Manager',
                        ],
                        true,
                    )
                    || $user->hasPermission(
                        'travel_insurance_policies.view',
                    );
                },
            )
            ->values();
    }

    private function alertContext(
        TravelInsurancePolicy $policy,
        CarbonInterface $today,
    ): ?array {
        if (
            $policy->approval_status
            !== 'Approved'
        ) {
            return null;
        }

        if (
            ! in_array(
                $policy->status,
                [
                    'Active',
                    'Expired',
                ],
                true,
            )
        ) {
            return null;
        }

        $expiry =
            $policy->policy_end_date;

        if (! $expiry) {
            return [
                'type' =>
                    'Policy Expiry Missing',

                'color' =>
                    'danger',

                'days_remaining' =>
                    null,

                'expiry_date' =>
                    null,
            ];
        }

        $expiry =
            $expiry
                ->copy()
                ->startOfDay();

        $daysRemaining =
            (int) $today->diffInDays(
                $expiry,
                false,
            );

        if ($daysRemaining < 0) {
            return [
                'type' =>
                    'Policy Expired',

                'color' =>
                    'danger',

                'days_remaining' =>
                    $daysRemaining,

                'expiry_date' =>
                    $expiry->toDateString(),
            ];
        }

        if (
            $daysRemaining
            <= self::CRITICAL_DAYS
        ) {
            return [
                'type' =>
                    'Policy Expiring Within 7 Days',

                'color' =>
                    'danger',

                'days_remaining' =>
                    $daysRemaining,

                'expiry_date' =>
                    $expiry->toDateString(),
            ];
        }

        if (
            $daysRemaining
            <= self::WARNING_DAYS
        ) {
            return [
                'type' =>
                    'Policy Expiring Within 30 Days',

                'color' =>
                    'warning',

                'days_remaining' =>
                    $daysRemaining,

                'expiry_date' =>
                    $expiry->toDateString(),
            ];
        }

        return null;
    }

    private function fingerprint(
        TravelInsurancePolicy $policy,
        array $context,
    ): string {
        $payload = [
            'policy_id' =>
                $policy->getKey(),

            'policy_no' =>
                $policy->policy_no,

            'customer_id' =>
                $policy->customer_id,

            'status' =>
                $policy->status,

            'approval_status' =>
                $policy->approval_status,

            'alert_type' =>
                $context['type'],

            'expiry_date' =>
                $context['expiry_date'],

            'days_remaining' =>
                $context['days_remaining'],
        ];

        return hash(
            'sha256',
            json_encode(
                $payload,
                JSON_UNESCAPED_SLASHES
                | JSON_UNESCAPED_UNICODE
                | JSON_THROW_ON_ERROR,
            ),
        );
    }

    private function notificationBody(
        TravelInsurancePolicy $policy,
        array $context,
    ): string {
        $parts = [
            'Policy: '
            .(
                $policy->policy_no
                ?: '#'.$policy->getKey()
            ),

            'Customer: '
            .(
                $policy->customer?->name
                ?: 'Not available'
            ),

            'Traveller: '
            .(
                $policy->traveller_name
                ?: 'Not available'
            ),

            'Provider: '
            .(
                $policy->provider?->display_name
                ?: $policy->provider?->name
                ?: 'Not available'
            ),
        ];

        if ($context['expiry_date']) {
            $parts[] =
                'Expiry: '
                .$context['expiry_date'];
        } else {
            $parts[] =
                'Expiry date has not been entered.';
        }

        if (
            is_int(
                $context['days_remaining'],
            )
        ) {
            if (
                $context['days_remaining']
                < 0
            ) {
                $parts[] =
                    'Expired '
                    .abs(
                        $context['days_remaining'],
                    )
                    .' day(s) ago.';
            } else {
                $parts[] =
                    'Days remaining: '
                    .$context['days_remaining']
                    .'.';
            }
        }

        return implode(
            ' | ',
            $parts,
        );
    }

    private function policyLabel(
        TravelInsurancePolicy $policy,
    ): string {
        return (
            $policy->policy_no
            ?: 'Policy #'.$policy->getKey()
        )
        .' — '
        .(
            $policy->traveller_name
            ?: $policy->customer?->name
            ?: 'Unknown traveller'
        );
    }
}