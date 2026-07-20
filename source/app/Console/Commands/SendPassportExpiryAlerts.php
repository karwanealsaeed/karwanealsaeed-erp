<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\VisaApplication;
use App\Services\PassportValidityService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class SendPassportExpiryAlerts extends Command
{
    private const EXPIRY_WARNING_DAYS = 180;

    protected $signature =
        'passport:send-expiry-alerts
        {--dry-run : Display alerts without sending or updating records}
        {--force : Send alerts even when the fingerprint was already sent}
        {--application= : Process only one Visa Application ID}';

    protected $description =
        'Send Passport expiry and journey-validity alerts to authorized staff.';

    public function handle(
        PassportValidityService $validityService,
    ): int {
        $recipients = $this->recipients();

        $query = VisaApplication::query()
            ->with([
                'customer',
                'country',
            ])
            ->where(
                function ($query): void {
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
            )
            ->orderBy('id');

        $applicationId =
            $this->option('application');

        if (filled($applicationId)) {
            $query->whereKey(
                (int) $applicationId,
            );
        }

        $applications = $query->get();

        if ($applications->isEmpty()) {
            $this->info(
                'No active Visa Applications found.',
            );

            return self::SUCCESS;
        }

        if ($recipients->isEmpty()) {
            $this->warn(
                'No active authorized recipients found. Validity statuses will still be refreshed.',
            );
        }

        $alerts = 0;
        $notifications = 0;
        $duplicates = 0;
        $resolved = 0;

        foreach ($applications as $application) {
            $result = $validityService->evaluate(
                passportExpiry:
                    $application
                        ->passport_expiry_date,

                travelDate:
                    $application->travel_date,

                returnDate:
                    $application->return_date,

                requiredMonths:
                    (int) (
                        $application
                            ->required_passport_validity_months
                        ?? PassportValidityService
                            ::DEFAULT_VALIDITY_MONTHS
                    ),
            );

            $alertType = $this->alertType(
                $result,
            );

            $derivedData = [
                'passport_required_until' =>
                    $result['required_until'],

                'passport_validity_status' =>
                    $result['status'],

                'passport_validity_checked_at' =>
                    now(),
            ];

            if ($alertType === null) {
                if (! $this->option('dry-run')) {
                    $hadAlert =
                        filled(
                            $application
                                ->passport_alert_fingerprint,
                        );

                    $application->forceFill(
                        array_merge(
                            $derivedData,
                            [
                                'passport_alert_fingerprint' =>
                                    null,

                                'passport_alert_sent_at' =>
                                    null,
                            ],
                        ),
                    )->saveQuietly();

                    if ($hadAlert) {
                        $resolved++;
                    }
                }

                continue;
            }

            $fingerprint =
                $this->fingerprint(
                    $application,
                    $alertType,
                    $result,
                );

            $alreadySent =
                $application
                    ->passport_alert_fingerprint
                === $fingerprint;

            if (
                $alreadySent
                && ! $this->option('force')
            ) {
                if (! $this->option('dry-run')) {
                    $application->forceFill(
                        $derivedData,
                    )->saveQuietly();
                }

                $duplicates++;

                continue;
            }

            $alerts++;

            if ($this->option('dry-run')) {
                if ($recipients->isEmpty()) {
                    $this->line(
                        "Would process {$alertType}: "
                        .$this->applicationLabel(
                            $application,
                        )
                        .' — no recipient available.',
                    );

                    continue;
                }

                foreach ($recipients as $recipient) {
                    $this->line(
                        "Would notify {$recipient->name}: "
                        ."{$alertType} — "
                        .$this->applicationLabel(
                            $application,
                        ),
                    );
                }

                continue;
            }

            if ($recipients->isEmpty()) {
                $application->forceFill(
                    $derivedData,
                )->saveQuietly();

                continue;
            }

            foreach ($recipients as $recipient) {
                $recipient->notifyNow(
                    Notification::make()
                        ->title(
                            'Passport Alert: '
                            .$alertType,
                        )
                        ->body(
                            $this->notificationBody(
                                $application,
                                $result,
                            ),
                        )
                        ->icon(
                            'heroicon-o-identification',
                        )
                        ->color(
                            $this->colorFor(
                                $alertType,
                            ),
                        )
                        ->actions([
                            Action::make('view')
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
                        ])
                        ->toDatabase(),
                );

                $notifications++;
            }

            $application->forceFill(
                array_merge(
                    $derivedData,
                    [
                        'passport_alert_fingerprint' =>
                            $fingerprint,

                        'passport_alert_sent_at' =>
                            now(),
                    ],
                ),
            )->saveQuietly();

            $this->info(
                "{$alertType}: "
                .$this->applicationLabel(
                    $application,
                )
                ." — {$recipients->count()} recipients.",
            );
        }

        if ($this->option('dry-run')) {
            $this->info(
                "Dry run completed: {$alerts} actionable alerts, "
                ."{$duplicates} duplicates.",
            );

            return self::SUCCESS;
        }

        $this->info(
            "Completed: {$alerts} alerts, "
            ."{$notifications} notifications, "
            ."{$duplicates} duplicates skipped, "
            ."{$resolved} resolved alerts reset.",
        );

        return self::SUCCESS;
    }

    private function recipients(): Collection
    {
        return User::query()
            ->where('is_active', true)
            ->get()
            ->filter(
                function (User $user): bool {
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

    private function alertType(
        array $result,
    ): ?string {
        if (
            in_array(
                $result['status'],
                [
                    PassportValidityService
                        ::STATUS_EXPIRED,

                    PassportValidityService
                        ::STATUS_NOT_VALID_FOR_JOURNEY,

                    PassportValidityService
                        ::STATUS_RENEWAL_REQUIRED,

                    PassportValidityService
                        ::STATUS_MISSING_EXPIRY,
                ],
                true,
            )
        ) {
            return $result['status'];
        }

        $expiry =
            $result['passport_expiry_date'];

        if (! $expiry) {
            return null;
        }

        $today = now()->startOfDay();

        if (
            $expiry->gte($today)
            && $expiry->lte(
                $today->copy()->addDays(
                    self::EXPIRY_WARNING_DAYS,
                ),
            )
        ) {
            return 'Passport Expiring Soon';
        }

        return null;
    }

    private function fingerprint(
        VisaApplication $application,
        string $alertType,
        array $result,
    ): string {
        $payload = [
            'application_id' =>
                $application->getKey(),

            'alert_type' =>
                $alertType,

            'validity_status' =>
                $result['status'],

            'passport_expiry_date' =>
                $result[
                    'passport_expiry_date'
                ]?->toDateString(),

            'travel_date' =>
                $application
                    ->travel_date
                    ?->toDateString(),

            'return_date' =>
                $application
                    ->return_date
                    ?->toDateString(),

            'required_until' =>
                $result[
                    'required_until'
                ]?->toDateString(),

            'required_months' =>
                $result['required_months'],
        ];

        return hash(
            'sha256',
            json_encode(
                $payload,
                JSON_THROW_ON_ERROR,
            ),
        );
    }

    private function notificationBody(
        VisaApplication $application,
        array $result,
    ): string {
        $details = [
            'Application: '
            .$this->applicationLabel(
                $application,
            ),
        ];

        if ($application->customer) {
            $details[] =
                'Customer: '
                .$application
                    ->customer
                    ->name;
        }

        if ($application->passport_no) {
            $details[] =
                'Passport: '
                .$application->passport_no;
        }

        if (
            $result[
                'passport_expiry_date'
            ]
        ) {
            $details[] =
                'Expiry: '
                .$result[
                    'passport_expiry_date'
                ]->format('d M Y');
        }

        if ($application->travel_date) {
            $details[] =
                'Travel: '
                .$application
                    ->travel_date
                    ->format('d M Y');
        }

        if ($result['required_until']) {
            $details[] =
                'Required until: '
                .$result[
                    'required_until'
                ]->format('d M Y');
        }

        $details[] =
            $result['message'];

        return implode(
            ' | ',
            $details,
        );
    }

    private function applicationLabel(
        VisaApplication $application,
    ): string {
        return filled(
            $application->application_no,
        )
            ? $application->application_no
            : 'Visa Application #'
                .$application->getKey();
    }

    private function colorFor(
        string $alertType,
    ): string {
        return match ($alertType) {
            PassportValidityService
                ::STATUS_RENEWAL_REQUIRED,
            'Passport Expiring Soon' =>
                'warning',

            default =>
                'danger',
        };
    }
}
