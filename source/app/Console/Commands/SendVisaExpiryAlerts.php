<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use App\Models\VisaApplication;
use Carbon\CarbonInterface;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SendVisaExpiryAlerts extends Command
{
    private const WARNING_DAYS =
        30;

    private const CRITICAL_DAYS =
        7;

    protected $signature =
        'visa:send-expiry-alerts
        {--dry-run : Show actions without sending or updating records}
        {--force : Send again even when the current fingerprint was already processed}';

    protected $description =
        'Send Visa expiry alerts to authorized active staff.';

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

        VisaApplication::query()
            ->with(
                [
                    'customer:id,name,mobile,email',
                    'country:id,name',
                    'visaType:id,name',
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
                            'visa_alert_fingerprint',
                        )
                        ->orWhereNotNull(
                            'visa_alert_sent_at',
                        )
                        ->orWhere(
                            function (
                                Builder $query,
                            ) use (
                                $today,
                            ): void {
                                $query
                                    ->whereIn(
                                        'status',
                                        [
                                            'Approved',
                                            'Delivered',
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
                                                    'visa_expiry_date',
                                                )
                                                ->orWhereDate(
                                                    'visa_expiry_date',
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
                    Collection $applications,
                ) use (
                    $today,
                    $recipients,
                    &$alerts,
                    &$notifications,
                    &$duplicates,
                    &$resolved,
                    &$withoutRecipients,
                ): void {
                    foreach ($applications as $application) {
                        $context =
                            $this->alertContext(
                                $application,
                                $today,
                            );

                        if ($context === null) {
                            $hadAlert =
                                filled(
                                    $application
                                        ->visa_alert_fingerprint,
                                )
                                || filled(
                                    $application
                                        ->visa_alert_sent_at,
                                );

                            if (
                                $hadAlert
                                && ! $this->option(
                                    'dry-run',
                                )
                            ) {
                                $application->forceFill(
                                    [
                                        'visa_alert_fingerprint' =>
                                            null,

                                        'visa_alert_sent_at' =>
                                            null,
                                    ],
                                )->saveQuietly();

                                $resolved++;
                            }

                            continue;
                        }

                        $fingerprint =
                            $this->fingerprint(
                                $application,
                                $context,
                            );

                        $alreadySent =
                            hash_equals(
                                (string) (
                                    $application
                                        ->visa_alert_fingerprint
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
                                    .$this->applicationLabel(
                                        $application,
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
                                    .$this->applicationLabel(
                                        $application,
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
                                .$this->applicationLabel(
                                    $application,
                                ),
                            );

                            continue;
                        }

                        foreach ($recipients as $recipient) {
                            $recipient->notifyNow(
                                Notification::make()
                                    ->title(
                                        'Visa Alert: '
                                        .$context['type'],
                                    )
                                    ->body(
                                        $this->notificationBody(
                                            $application,
                                            $context,
                                        ),
                                    )
                                    ->icon(
                                        'heroicon-o-document-check',
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
                                                    'View Visa Application',
                                                )
                                                ->button()
                                                ->url(
                                                    route(
                                                        'filament.admin.resources.'
                                                        .'visa-applications.view',
                                                        [
                                                            'record' =>
                                                                $application,
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

                        $application->forceFill(
                            [
                                'visa_alert_fingerprint' =>
                                    $fingerprint,

                                'visa_alert_sent_at' =>
                                    now(
                                        'Asia/Karachi',
                                    ),
                            ],
                        )->saveQuietly();

                        $this->info(
                            $context['type']
                            .': '
                            .$this->applicationLabel(
                                $application,
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
                        'visa_applications.view',
                    );
                },
            )
            ->values();
    }

    private function alertContext(
        VisaApplication $application,
        CarbonInterface $today,
    ): ?array {
        if (
            ! in_array(
                $application->status,
                [
                    'Approved',
                    'Delivered',
                ],
                true,
            )
        ) {
            return null;
        }

        $expiry =
            $application->visa_expiry_date;

        if (! $expiry) {
            return [
                'type' =>
                    'Visa Expiry Missing',

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
                    'Visa Expired',

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
                    'Visa Expiring Within 7 Days',

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
                    'Visa Expiring Within 30 Days',

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
        VisaApplication $application,
        array $context,
    ): string {
        $payload = [
            'application_id' =>
                $application->getKey(),

            'application_no' =>
                $application->application_no,

            'customer_id' =>
                $application->customer_id,

            'status' =>
                $application->status,

            'alert_type' =>
                $context['type'],

            'visa_issue_date' =>
                $application
                    ->visa_issue_date
                    ?->toDateString(),

            'visa_expiry_date' =>
                $context['expiry_date'],
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
        VisaApplication $application,
        array $context,
    ): string {
        $parts = [
            'Application: '
            .(
                $application->application_no
                ?: '#'.$application->getKey()
            ),

            'Customer: '
            .(
                $application->customer?->name
                ?: 'Not available'
            ),

            'Country: '
            .(
                $application->country?->name
                ?: 'Not available'
            ),

            'Visa Type: '
            .(
                $application->visaType?->name
                ?: 'Not available'
            ),

            'Passport: '
            .(
                $application->passport_no
                ?: 'Not available'
            ),
        ];

        if ($application->visa_issue_date) {
            $parts[] =
                'Issued: '
                .$application
                    ->visa_issue_date
                    ->format(
                        'd M Y',
                    );
        }

        if ($context['expiry_date']) {
            $parts[] =
                'Expiry: '
                .\Carbon\CarbonImmutable::parse(
                    $context['expiry_date'],
                )->format(
                    'd M Y',
                );
        } else {
            $parts[] =
                'Visa expiry date has not been entered.';
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

    private function applicationLabel(
        VisaApplication $application,
    ): string {
        return (
            $application->application_no
            ?: 'Visa Application #'
                .$application->getKey()
        )
        .' — '
        .(
            $application->customer?->name
            ?: $application->passport_no
            ?: 'Unknown customer'
        );
    }
}
