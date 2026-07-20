<?php

return [
    'retention_days' => max(
        1,
        (int) env(
            'DATABASE_BACKUP_RETENTION_DAYS',
            30,
        ),
    ),

    'minimum_backups' => max(
        1,
        (int) env(
            'DATABASE_BACKUP_MINIMUM_COPIES',
            7,
        ),
    ),
];