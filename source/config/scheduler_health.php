<?php

return [
    'stale_after_minutes' => max(
        2,
        (int) env(
            'SCHEDULER_STALE_AFTER_MINUTES',
            3,
        ),
    ),

    'alert_cooldown_minutes' => max(
        5,
        (int) env(
            'SCHEDULER_ALERT_COOLDOWN_MINUTES',
            360,
        ),
    ),

    'max_pending_jobs' => max(
        0,
        (int) env(
            'QUEUE_MAX_PENDING_JOBS',
            100,
        ),
    ),
];