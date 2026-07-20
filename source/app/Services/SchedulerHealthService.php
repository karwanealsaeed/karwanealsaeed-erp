<?php

namespace App\Services;

use App\Models\SchedulerHeartbeat;
use App\Models\User;
use Carbon\CarbonInterface;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SchedulerHealthService
{
    public const MONITOR_KEY =
        'laravel-scheduler';

    public function markStarted(): SchedulerHeartbeat
    {
        $heartbeat =
            $this->heartbeat();

        $heartbeat->update([
            'last_started_at' =>
                now('Asia/Karachi'),
        ]);

        return $heartbeat->fresh();
    }

    public function markSuccess(
        ?CarbonInterface $startedAt = null,
    ): SchedulerHeartbeat {
        $heartbeat =
            $this->heartbeat();

        $wasAlerted =
            filled(
                $heartbeat->last_alerted_at,
            );

        $finishedAt =
            now('Asia/Karachi');

        $duration =
            $startedAt
                ? max(
                    0,
                    (int) $startedAt
                        ->diffInMilliseconds(
                            $finishedAt,
                        ),
                )
                : null;

        $heartbeat->update([
            'last_finished_at' =>
                $finishedAt,

            'last_success_at' =>
                $finishedAt,

            'last_duration_ms' =>
                $duration,

            'last_exit_code' =>
                0,

            'consecutive_failures' =>
                0,

            'failure_message' =>
                null,

            'last_alerted_at' =>
                null,
        ]);

        if ($wasAlerted) {
            $this->sendRecoveryNotification(
                $heartbeat->fresh(),
            );
        }

        return $heartbeat->fresh();
    }

    public function markFailure(
        Throwable|string $error,
        ?CarbonInterface $startedAt = null,
        int $exitCode = 1,
    ): SchedulerHeartbeat {
        $heartbeat =
            $this->heartbeat();

        $finishedAt =
            now('Asia/Karachi');

        $message =
            $error instanceof Throwable
                ? $error->getMessage()
                : $error;

        $duration =
            $startedAt
                ? max(
                    0,
                    (int) $startedAt
                        ->diffInMilliseconds(
                            $finishedAt,
                        ),
                )
                : null;

        $heartbeat->update([
            'last_finished_at' =>
                $finishedAt,

            'last_failed_at' =>
                $finishedAt,

            'last_duration_ms' =>
                $duration,

            'last_exit_code' =>
                $exitCode,

            'consecutive_failures' =>
                ((int)
                    $heartbeat
                        ->consecutive_failures)
                + 1,

            'failure_message' =>
                mb_substr(
                    $message,
                    0,
                    2000,
                ),
        ]);

        return $heartbeat->fresh();
    }

    public function status(): array
    {
        $heartbeat =
            SchedulerHeartbeat::query()
                ->where(
                    'monitor_key',
                    self::MONITOR_KEY,
                )
                ->first();

        $staleAfterMinutes =
            $this->staleAfterMinutes();

        if (
            ! $heartbeat
            || ! $heartbeat->last_success_at
        ) {
            return [
                'healthy' =>
                    false,

                'state' =>
                    'Never Run',

                'message' =>
                    'Scheduler heartbeat has never completed. Configure php artisan schedule:run to execute every minute.',

                'last_success_at' =>
                    null,

                'age_seconds' =>
                    null,

                'stale_after_minutes' =>
                    $staleAfterMinutes,

                'consecutive_failures' =>
                    (int) (
                        $heartbeat
                            ?->consecutive_failures
                        ?? 0
                    ),

                'failure_message' =>
                    $heartbeat
                        ?->failure_message,
            ];
        }

        $ageSeconds =
            (int) $heartbeat
                ->last_success_at
                ->diffInSeconds(
                    now('Asia/Karachi'),
                );

        $hasFailures =
            ((int)
                $heartbeat
                    ->consecutive_failures)
            > 0;

        $isStale =
            $ageSeconds
            > ($staleAfterMinutes * 60);

        $healthy =
            ! $isStale
            && ! $hasFailures;

        if ($hasFailures) {
            $state =
                'Failed';

            $message =
                'Scheduler heartbeat recorded '
                .$heartbeat
                    ->consecutive_failures
                .' consecutive failure(s).';

            if (
                filled(
                    $heartbeat
                        ->failure_message,
                )
            ) {
                $message .=
                    ' '
                    .$heartbeat
                        ->failure_message;
            }
        } elseif ($isStale) {
            $state =
                'Stale';

            $message =
                'Scheduler last succeeded '
                .$heartbeat
                    ->last_success_at
                    ->diffForHumans()
                .'. Expected every minute.';
        } else {
            $state =
                'Healthy';

            $message =
                'Scheduler heartbeat succeeded '
                .$heartbeat
                    ->last_success_at
                    ->diffForHumans()
                .'.';
        }

        return [
            'healthy' =>
                $healthy,

            'state' =>
                $state,

            'message' =>
                $message,

            'last_success_at' =>
                $heartbeat
                    ->last_success_at,

            'age_seconds' =>
                $ageSeconds,

            'stale_after_minutes' =>
                $staleAfterMinutes,

            'consecutive_failures' =>
                (int)
                $heartbeat
                    ->consecutive_failures,

            'failure_message' =>
                $heartbeat
                    ->failure_message,
        ];
    }

    public function queueStatus(): array
    {
        $jobsTableExists =
            Schema::hasTable('jobs');

        $failedJobsTableExists =
            Schema::hasTable(
                'failed_jobs',
            );

        $pendingJobs =
            $jobsTableExists
                ? (int) DB::table('jobs')
                    ->count()
                : 0;

        $failedJobs =
            $failedJobsTableExists
                ? (int) DB::table(
                    'failed_jobs',
                )->count()
                : 0;

        $maximumPending =
            max(
                0,
                (int) config(
                    'scheduler_health.max_pending_jobs',
                    100,
                ),
            );

        return [
            'connection' =>
                (string) config(
                    'queue.default',
                    'sync',
                ),

            'jobs_table_exists' =>
                $jobsTableExists,

            'failed_jobs_table_exists' =>
                $failedJobsTableExists,

            'tables_ready' =>
                $jobsTableExists
                && $failedJobsTableExists,

            'pending_jobs' =>
                $pendingJobs,

            'failed_jobs' =>
                $failedJobs,

            'maximum_pending_jobs' =>
                $maximumPending,

            'pending_healthy' =>
                $pendingJobs
                <= $maximumPending,

            'failed_healthy' =>
                $failedJobs === 0,
        ];
    }

    public function alertIfStale(): bool
    {
        $status =
            $this->status();

        if ($status['healthy']) {
            return false;
        }

        $heartbeat =
            $this->heartbeat();

        $cooldownMinutes =
            max(
                5,
                (int) config(
                    'scheduler_health.alert_cooldown_minutes',
                    360,
                ),
            );

        if (
            $heartbeat->last_alerted_at
            && $heartbeat
                ->last_alerted_at
                ->greaterThan(
                    now('Asia/Karachi')
                        ->subMinutes(
                            $cooldownMinutes,
                        ),
                )
        ) {
            return false;
        }

        $recipients =
            $this->recipients();

        if ($recipients->isEmpty()) {
            return false;
        }

        Notification::make()
            ->title(
                'Scheduler health warning',
            )
            ->body(
                $status['message']
                .' Scheduled backups, task reminders and passport alerts may not run.',
            )
            ->icon(
                'heroicon-o-exclamation-triangle',
            )
            ->danger()
            ->sendToDatabase(
                $recipients,
            );

        $heartbeat->update([
            'last_alerted_at' =>
                now('Asia/Karachi'),
        ]);

        return true;
    }

    private function sendRecoveryNotification(
        SchedulerHeartbeat $heartbeat,
    ): void {
        $recipients =
            $this->recipients();

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::make()
            ->title(
                'Scheduler recovered',
            )
            ->body(
                'Laravel scheduler heartbeat is healthy again at '
                .$heartbeat
                    ->last_success_at
                    ?->format(
                        'd M Y h:i A',
                    )
                .'.',
            )
            ->icon(
                'heroicon-o-check-circle',
            )
            ->success()
            ->sendToDatabase(
                $recipients,
            );
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

    private function heartbeat(): SchedulerHeartbeat
    {
        return SchedulerHeartbeat::query()
            ->firstOrCreate(
                [
                    'monitor_key' =>
                        self::MONITOR_KEY,
                ],
                [
                    'consecutive_failures' =>
                        0,
                ],
            );
    }

    private function staleAfterMinutes(): int
    {
        return max(
            2,
            (int) config(
                'scheduler_health.stale_after_minutes',
                3,
            ),
        );
    }
}