<?php

namespace App\Console\Commands;

use App\Models\SystemErrorIncident;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use SplFileInfo;

class MaintainErrorMonitoring extends Command
{
    protected $signature =
        'system:maintain-errors
        {--resolved-days= : Days to retain resolved incidents}
        {--log-days= : Days to retain rotated log files}
        {--dry-run : Display changes without deleting records or files}';

    protected $description =
        'Prune resolved error incidents and old rotated log files';

    public function handle(): int
    {
        $resolvedDays =
            max(
                1,
                (int) (
                    $this->option(
                        'resolved-days',
                    )
                    ?: config(
                        'error_monitoring.resolved_retention_days',
                        30,
                    )
                ),
            );

        $logDays =
            max(
                1,
                (int) (
                    $this->option(
                        'log-days',
                    )
                    ?: config(
                        'error_monitoring.log_retention_days',
                        14,
                    )
                ),
            );

        $dryRun =
            (bool) $this->option(
                'dry-run',
            );

        $resolvedCount =
            $this->pruneResolved(
                $resolvedDays,
                $dryRun,
            );

        $logCount =
            $this->pruneLogs(
                $logDays,
                $dryRun,
            );

        $mode =
            $dryRun
                ? 'Dry run'
                : 'Completed';

        $this->info(
            "{$mode}: {$resolvedCount} resolved incidents and "
            ."{$logCount} rotated log files matched.",
        );

        return self::SUCCESS;
    }

    private function pruneResolved(
        int $days,
        bool $dryRun,
    ): int {
        if (
            ! Schema::hasTable(
                'system_error_incidents',
            )
        ) {
            return 0;
        }

        $query =
            SystemErrorIncident::query()
                ->resolved()
                ->where(
                    'resolved_at',
                    '<',
                    now()->subDays(
                        $days,
                    ),
                );

        $count =
            $query->count();

        if (
            ! $dryRun
            && $count > 0
        ) {
            $query->delete();
        }

        return $count;
    }

    private function pruneLogs(
        int $days,
        bool $dryRun,
    ): int {
        $directory =
            storage_path(
                'logs',
            );

        if (! is_dir($directory)) {
            return 0;
        }

        $cutoff =
            now()
                ->subDays(
                    $days,
                )
                ->getTimestamp();

        $matched =
            0;

        foreach (
            File::files(
                $directory,
            )
            as $file
        ) {
            if (
                ! $this->isRotatedLog(
                    $file,
                )
            ) {
                continue;
            }

            if (
                $file->getMTime()
                >= $cutoff
            ) {
                continue;
            }

            $matched++;

            if (! $dryRun) {
                File::delete(
                    $file->getPathname(),
                );
            }
        }

        return $matched;
    }

    private function isRotatedLog(
        SplFileInfo $file,
    ): bool {
        $name =
            $file->getFilename();

        $isLaravelDaily =
            preg_match(
                '/^laravel-\d{4}-\d{2}-\d{2}\.log$/',
                $name,
            )
            === 1;

        $isLaravelArchive =
            preg_match(
                '/^laravel-.+\.log$/',
                $name,
            )
            === 1;

        $isSchedulerRotation =
            preg_match(
                '/^scheduler\.log\.\d+$/',
                $name,
            )
            === 1;

        return $isLaravelDaily
            || $isLaravelArchive
            || $isSchedulerRotation;
    }
}