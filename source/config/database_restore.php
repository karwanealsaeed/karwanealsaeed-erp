<?php

return [
    'enabled' =>
        filter_var(
            env(
                'DATABASE_RESTORE_ENABLED',
                true,
            ),
            FILTER_VALIDATE_BOOLEAN,
        ),

    'execution_enabled' =>
        filter_var(
            env(
                'DATABASE_RESTORE_EXECUTION_ENABLED',
                false,
            ),
            FILTER_VALIDATE_BOOLEAN,
        ),

    'mysql_binary' =>
        env(
            'DATABASE_RESTORE_MYSQL_BINARY',
        ),

    'timeout_seconds' =>
        max(
            60,
            (int) env(
                'DATABASE_RESTORE_TIMEOUT_SECONDS',
                900,
            ),
        ),

    'max_file_bytes' =>
        max(
            1048576,
            (int) env(
                'DATABASE_RESTORE_MAX_FILE_BYTES',
                1073741824,
            ),
        ),

    'allowed_environments' =>
        array_values(
            array_filter(
                array_map(
                    'trim',
                    explode(
                        ',',
                        (string) env(
                            'DATABASE_RESTORE_ALLOWED_ENVIRONMENTS',
                            'local,staging,production',
                        ),
                    ),
                ),
            ),
        ),

    'allow_testing_non_mysql' =>
        false,

    'required_markers' => [
        'SET FOREIGN_KEY_CHECKS=0;',
        'START TRANSACTION;',
        'CREATE TABLE',
        'COMMIT;',
        'SET FOREIGN_KEY_CHECKS=1;',
    ],

    'unsafe_patterns' => [
        '/\bDROP\s+DATABASE\b/i',
        '/\bCREATE\s+DATABASE\b/i',
        '/\bALTER\s+USER\b/i',
        '/\bCREATE\s+USER\b/i',
        '/\bDROP\s+USER\b/i',
        '/\bGRANT\s+/i',
        '/\bREVOKE\s+/i',
        '/\bSHUTDOWN\b/i',
        '/\bINTO\s+OUTFILE\b/i',
        '/\bINTO\s+DUMPFILE\b/i',
        '/\bLOAD\s+DATA\b/i',
        '/^\s*SOURCE\s+/mi',
        '/\bINSTALL\s+PLUGIN\b/i',
        '/\bUNINSTALL\s+PLUGIN\b/i',
    ],
];