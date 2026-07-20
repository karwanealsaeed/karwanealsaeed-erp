<?php

namespace App\Console\Commands;

use App\Services\SchedulerHealthService;
use Illuminate\Console\Command;
use Throwable;

class RecordSchedulerHeartbeat extends Command
{
    protected $signature =
        'system:scheduler-heartbeat';

    protected $description =
        'Record Laravel scheduler heartbeat and recovery status';

    public function handle(
        SchedulerHealthService $healthService,
    ): int {
        $startedAt =
            now('Asia/Karachi');

        try {
            $healthService->markStarted();

            $heartbeat =
                $healthService->markSuccess(
                    $startedAt,
                );

            $this->info(
                'Scheduler heartbeat recorded at '
                .$heartbeat
                    ->last_success_at
                    ?->format(
                        'Y-m-d H:i:s',
                    ),
            );

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);

            try {
                $healthService->markFailure(
                    $exception,
                    $startedAt,
                    self::FAILURE,
                );
            } catch (Throwable $trackingException) {
                report(
                    $trackingException,
                );
            }

            $this->error(
                'Scheduler heartbeat failed: '
                .$exception->getMessage(),
            );

            return self::FAILURE;
        }
    }
}