<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Symfony\Component\Process\Process;

class MysqlDatabaseRestoreExecutor
{
    public function import(
        string $sqlPath,
        string $databaseName,
    ): array {
        if (! is_file($sqlPath)) {
            throw new RuntimeException(
                'Restore SQL file missing hai.',
            );
        }

        $connectionName =
            (string) config(
                'database.default',
            );

        $connection =
            (array) config(
                'database.connections.'
                .$connectionName,
                [],
            );

        if (
            ($connection['driver'] ?? null)
            !== 'mysql'
        ) {
            throw new RuntimeException(
                'MySQL restore executor ko MySQL connection required hai.',
            );
        }

        $binary =
            trim(
                (string) config(
                    'database_restore.mysql_binary',
                ),
            );

        if (
            $binary === ''
            || ! is_file($binary)
        ) {
            throw new RuntimeException(
                'Configured mysql client executable nahi mili.',
            );
        }

        $host =
            (string) (
                $connection['host']
                ?? '127.0.0.1'
            );

        $port =
            (string) (
                $connection['port']
                ?? '3306'
            );

        $username =
            (string) (
                $connection['username']
                ?? ''
            );

        $password =
            (string) (
                $connection['password']
                ?? ''
            );

        if (
            $username === ''
            || $databaseName === ''
        ) {
            throw new RuntimeException(
                'Database username ya database name missing hai.',
            );
        }

        $stream =
            fopen(
                $sqlPath,
                'rb',
            );

        if (! is_resource($stream)) {
            throw new RuntimeException(
                'Restore SQL stream open nahi hui.',
            );
        }

        $command = [
            $binary,
            '--protocol=TCP',
            '--host='.$host,
            '--port='.$port,
            '--user='.$username,
            '--default-character-set=utf8mb4',
            '--database='.$databaseName,
        ];

        $process =
            new Process(
                $command,
                base_path(),
                [
                    'MYSQL_PWD' =>
                        $password,
                ],
            );

        $process->setInput(
            $stream,
        );

        $process->setTimeout(
            (float) config(
                'database_restore.timeout_seconds',
                900,
            ),
        );

        $startedAt =
            microtime(
                true,
            );

        try {
            $process->run();
        } finally {
            fclose(
                $stream,
            );
        }

        if (! $process->isSuccessful()) {
            throw new RuntimeException(
                'MySQL restore import failed: '
                .(
                    trim(
                        $process->getErrorOutput(),
                    )
                    ?: trim(
                        $process->getOutput(),
                    )
                    ?: 'Unknown MySQL import error.'
                ),
            );
        }

        return [
            'exit_code' =>
                $process->getExitCode(),

            'duration_seconds' =>
                round(
                    microtime(true)
                    - $startedAt,
                    3,
                ),

            'output' =>
                trim(
                    $process->getOutput(),
                ),
        ];
    }

    public function reconnect(): void
    {
        $connectionName =
            (string) config(
                'database.default',
            );

        DB::purge(
            $connectionName,
        );

        DB::reconnect(
            $connectionName,
        );
    }

    public function validate(
        array $requiredTables,
    ): array {
        $missingTables = [];

        foreach ($requiredTables as $table) {
            if (
                ! Schema::hasTable(
                    (string) $table,
                )
            ) {
                $missingTables[] =
                    (string) $table;
            }
        }

        if (count($missingTables) > 0) {
            throw new RuntimeException(
                'Restored database required tables missing: '
                .implode(
                    ', ',
                    $missingTables,
                ),
            );
        }

        $migrationCount =
            Schema::hasTable(
                'migrations',
            )
                ? DB::table(
                    'migrations',
                )->count()
                : 0;

        if ($migrationCount <= 0) {
            throw new RuntimeException(
                'Restored migrations table empty hai.',
            );
        }

        return [
            'required_tables' =>
                array_values(
                    $requiredTables,
                ),

            'missing_tables' =>
                [],

            'migration_count' =>
                $migrationCount,
        ];
    }
}