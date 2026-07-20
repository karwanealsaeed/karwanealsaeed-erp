<?php

namespace App\Services;

use App\Models\SystemErrorIncident;
use App\Models\User;
use Error;
use Filament\Notifications\Notification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use ParseError;
use PDOException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;
use TypeError;

class SystemErrorService
{
    public function capture(
        Throwable $exception,
    ): ?SystemErrorIncident {
        if (! $this->isEnabled()) {
            return null;
        }

        $captureTesting =
            (bool) config(
                'error_monitoring.capture_testing',
                false,
            );

        if (
            app()->environment('testing')
            && ! $captureTesting
        ) {
            return null;
        }

        if (
            ! Schema::hasTable(
                'system_error_incidents',
            )
        ) {
            return null;
        }

        if (
            $this->shouldIgnore(
                $exception,
            )
        ) {
            return null;
        }

        $payload =
            $this->payload(
                $exception,
            );

        [$incident, $shouldNotify] =
            DB::transaction(
                function () use (
                    $payload,
                ): array {
                    $incident =
                        SystemErrorIncident::query()
                            ->where(
                                'fingerprint',
                                $payload[
                                    'fingerprint'
                                ],
                            )
                            ->lockForUpdate()
                            ->first();

                    $isNew =
                        ! $incident;

                    $wasResolved =
                        filled(
                            $incident
                                ?->resolved_at,
                        );

                    if (! $incident) {
                        $incident =
                            SystemErrorIncident::query()
                                ->create(
                                    $payload,
                                );
                    } else {
                        $incident->update([
                            'severity' =>
                                $payload[
                                    'severity'
                                ],

                            'environment' =>
                                $payload[
                                    'environment'
                                ],

                            'exception_class' =>
                                $payload[
                                    'exception_class'
                                ],

                            'message' =>
                                $payload[
                                    'message'
                                ],

                            'file' =>
                                $payload[
                                    'file'
                                ],

                            'line' =>
                                $payload[
                                    'line'
                                ],

                            'route_name' =>
                                $payload[
                                    'route_name'
                                ],

                            'request_method' =>
                                $payload[
                                    'request_method'
                                ],

                            'request_path' =>
                                $payload[
                                    'request_path'
                                ],

                            'user_id' =>
                                $payload[
                                    'user_id'
                                ],

                            'user_email' =>
                                $payload[
                                    'user_email'
                                ],

                            'occurrences' =>
                                ((int)
                                    $incident
                                        ->occurrences)
                                + 1,

                            'last_seen_at' =>
                                $payload[
                                    'last_seen_at'
                                ],

                            'context' =>
                                $payload[
                                    'context'
                                ],

                            'sample_trace' =>
                                $payload[
                                    'sample_trace'
                                ],

                            'resolved_at' =>
                                null,

                            'resolved_by' =>
                                null,

                            'resolution_note' =>
                                null,
                        ]);
                    }

                    $shouldNotify =
                        $this->shouldNotify(
                            $incident,
                            $isNew,
                            $wasResolved,
                        );

                    return [
                        $incident->fresh(),
                        $shouldNotify,
                    ];
                },
            );

        if (
            $shouldNotify
            && $this->notifyAdmins(
                $incident,
            )
        ) {
            $incident->update([
                'last_notified_at' =>
                    now(),
            ]);
        }

        return $incident->fresh();
    }

    public function resolve(
        SystemErrorIncident $incident,
        ?User $user = null,
        ?string $note = null,
    ): SystemErrorIncident {
        $incident->update([
            'resolved_at' =>
                now(),

            'resolved_by' =>
                $user?->getKey(),

            'resolution_note' =>
                filled($note)
                    ? mb_substr(
                        trim($note),
                        0,
                        2000,
                    )
                    : null,
        ]);

        return $incident->fresh();
    }

    public function reopen(
        SystemErrorIncident $incident,
    ): SystemErrorIncident {
        $incident->update([
            'resolved_at' =>
                null,

            'resolved_by' =>
                null,

            'resolution_note' =>
                null,
        ]);

        return $incident->fresh();
    }

    public function summary(): array
    {
        if (
            ! Schema::hasTable(
                'system_error_incidents',
            )
        ) {
            return [
                'total' =>
                    0,

                'unresolved' =>
                    0,

                'critical' =>
                    0,

                'error' =>
                    0,

                'resolved' =>
                    0,

                'last_24_hours' =>
                    0,
            ];
        }

        return [
            'total' =>
                SystemErrorIncident::query()
                    ->count(),

            'unresolved' =>
                SystemErrorIncident::query()
                    ->unresolved()
                    ->count(),

            'critical' =>
                SystemErrorIncident::query()
                    ->unresolved()
                    ->severity(
                        'critical',
                    )
                    ->count(),

            'error' =>
                SystemErrorIncident::query()
                    ->unresolved()
                    ->severity(
                        'error',
                    )
                    ->count(),

            'resolved' =>
                SystemErrorIncident::query()
                    ->resolved()
                    ->count(),

            'last_24_hours' =>
                SystemErrorIncident::query()
                    ->where(
                        'last_seen_at',
                        '>=',
                        now()->subDay(),
                    )
                    ->count(),
        ];
    }

    private function payload(
        Throwable $exception,
    ): array {
        $rawMessage =
            filled(
                $exception->getMessage(),
            )
                ? $exception->getMessage()
                : class_basename(
                    $exception,
                );

        $message =
            $this->redact(
                $rawMessage,
            );

        $file =
            $this->relativePath(
                $exception->getFile(),
            );

        $requestData =
            $this->requestData();

        $userData =
            $this->userData();

        $fingerprintSource =
            get_class($exception)
            .'|'
            .$file
            .'|'
            .$exception->getLine()
            .'|'
            .$this->normalizeMessage(
                $rawMessage,
            );

        $now =
            now();

        return [
            'fingerprint' =>
                hash(
                    'sha256',
                    $fingerprintSource,
                ),

            'severity' =>
                $this->severity(
                    $exception,
                ),

            'environment' =>
                app()->environment(),

            'exception_class' =>
                get_class(
                    $exception,
                ),

            'message' =>
                mb_substr(
                    $message,
                    0,
                    (int) config(
                        'error_monitoring.max_message_length',
                        2000,
                    ),
                ),

            'file' =>
                $file,

            'line' =>
                $exception->getLine(),

            'route_name' =>
                $requestData[
                    'route_name'
                ],

            'request_method' =>
                $requestData[
                    'request_method'
                ],

            'request_path' =>
                $requestData[
                    'request_path'
                ],

            'user_id' =>
                $userData[
                    'user_id'
                ],

            'user_email' =>
                $userData[
                    'user_email'
                ],

            'occurrences' =>
                1,

            'first_seen_at' =>
                $now,

            'last_seen_at' =>
                $now,

            'context' => [
                'php_sapi' =>
                    PHP_SAPI,

                'console' =>
                    app()->runningInConsole(),

                'request_method' =>
                    $requestData[
                        'request_method'
                    ],

                'request_path' =>
                    $requestData[
                        'request_path'
                    ],

                'route_name' =>
                    $requestData[
                        'route_name'
                    ],
            ],

            'sample_trace' =>
                mb_substr(
                    $this->redact(
                        $exception
                            ->getTraceAsString(),
                    ),
                    0,
                    (int) config(
                        'error_monitoring.max_trace_length',
                        12000,
                    ),
                ),
        ];
    }

    private function requestData(): array
    {
        $empty = [
            'route_name' =>
                null,

            'request_method' =>
                null,

            'request_path' =>
                null,
        ];

        if (
            app()->runningInConsole()
            || ! app()->bound('request')
        ) {
            return $empty;
        }

        try {
            $request =
                request();

            return [
                'route_name' =>
                    $request
                        ->route()
                        ?->getName(),

                'request_method' =>
                    $request
                        ->method(),

                'request_path' =>
                    '/'.ltrim(
                        $request->path(),
                        '/',
                    ),
            ];
        } catch (Throwable) {
            return $empty;
        }
    }

    private function userData(): array
    {
        try {
            $user =
                Auth::user();

            return [
                'user_id' =>
                    $user?->getKey(),

                'user_email' =>
                    $user?->email,
            ];
        } catch (Throwable) {
            return [
                'user_id' =>
                    null,

                'user_email' =>
                    null,
            ];
        }
    }

    private function severity(
        Throwable $exception,
    ): string {
        if (
            $exception instanceof Error
            || $exception instanceof ParseError
            || $exception instanceof TypeError
            || $exception instanceof QueryException
            || $exception instanceof PDOException
        ) {
            return 'critical';
        }

        return 'error';
    }

    private function shouldIgnore(
        Throwable $exception,
    ): bool {
        if (
            $exception instanceof ValidationException
            || $exception instanceof AuthenticationException
            || $exception instanceof AuthorizationException
            || $exception instanceof ModelNotFoundException
        ) {
            return true;
        }

        if (
            $exception
            instanceof HttpExceptionInterface
        ) {
            return $exception
                ->getStatusCode()
                < 500;
        }

        foreach (
            (array) config(
                'error_monitoring.ignored_exceptions',
                [],
            )
            as $ignoredClass
        ) {
            if (
                is_string(
                    $ignoredClass,
                )
                && is_a(
                    $exception,
                    $ignoredClass,
                )
            ) {
                return true;
            }
        }

        return false;
    }

    private function shouldNotify(
        SystemErrorIncident $incident,
        bool $isNew,
        bool $wasResolved,
    ): bool {
        $severities =
            (array) config(
                'error_monitoring.alert_severities',
                [
                    'critical',
                    'error',
                ],
            );

        if (
            ! in_array(
                $incident->severity,
                $severities,
                true,
            )
        ) {
            return false;
        }

        if (
            $isNew
            || $wasResolved
            || ! $incident->last_notified_at
        ) {
            return true;
        }

        $cooldown =
            max(
                5,
                (int) config(
                    'error_monitoring.alert_cooldown_minutes',
                    60,
                ),
            );

        return $incident
            ->last_notified_at
            ->lte(
                now()->subMinutes(
                    $cooldown,
                ),
            );
    }

    private function notifyAdmins(
        SystemErrorIncident $incident,
    ): bool {
        try {
            $recipients =
                $this->recipients();

            if ($recipients->isEmpty()) {
                return false;
            }

            $title =
                $incident->severity
                === 'critical'
                    ? 'Critical ERP error detected'
                    : 'ERP error detected';

            $location =
                filled(
                    $incident->file,
                )
                    ? ' | '
                        .$incident->file
                        .':'
                        .$incident->line
                    : '';

            Notification::make()
                ->title(
                    $title,
                )
                ->body(
                    mb_substr(
                        $incident->message,
                        0,
                        500,
                    )
                    .$location,
                )
                ->icon(
                    'heroicon-o-bug-ant',
                )
                ->danger()
                ->sendToDatabase(
                    $recipients,
                );

            return true;
        } catch (Throwable) {
            return false;
        }
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
                fn (User $user): bool =>
                    $user->isAdmin()
                    || $user->hasPermission(
                        'settings.manage',
                    ),
            )
            ->values();
    }

    private function normalizeMessage(
        string $message,
    ): string {
        $normalized =
            preg_replace(
                '/[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}/i',
                '{uuid}',
                $message,
            )
            ?? $message;

        $normalized =
            preg_replace(
                '/\b\d+\b/',
                '#',
                $normalized,
            )
            ?? $normalized;

        $normalized =
            preg_replace(
                '/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i',
                '{email}',
                $normalized,
            )
            ?? $normalized;

        $normalized =
            preg_replace(
                '/\s+/',
                ' ',
                $normalized,
            )
            ?? $normalized;

        return mb_strtolower(
            trim(
                $normalized,
            ),
        );
    }

    private function redact(
        string $value,
    ): string {
        $redacted =
            preg_replace(
                '/\b(password|token|secret|authorization|api[_-]?key)(\s*[=:]\s*)([^,\s;]+)/i',
                '$1$2[REDACTED]',
                $value,
            )
            ?? $value;

        $redacted =
            preg_replace(
                '/Bearer\s+[A-Za-z0-9._~+\/=-]+/i',
                'Bearer [REDACTED]',
                $redacted,
            )
            ?? $redacted;

        return $redacted;
    }

    private function relativePath(
        string $path,
    ): string {
        $normalizedPath =
            str_replace(
                '\\',
                '/',
                $path,
            );

        $base =
            rtrim(
                str_replace(
                    '\\',
                    '/',
                    base_path(),
                ),
                '/',
            );

        if (
            str_starts_with(
                $normalizedPath,
                $base.'/',
            )
        ) {
            return substr(
                $normalizedPath,
                strlen($base) + 1,
            );
        }

        return $normalizedPath;
    }

    private function isEnabled(): bool
    {
        return (bool) config(
            'error_monitoring.enabled',
            true,
        );
    }
}