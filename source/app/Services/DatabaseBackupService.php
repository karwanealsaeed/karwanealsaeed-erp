<?php

namespace App\Services;

use App\Models\DatabaseBackup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class DatabaseBackupService
{
    private const REQUIRED_SQL_MARKERS = [
        'SET FOREIGN_KEY_CHECKS=0;',
        'START TRANSACTION;',
        'CREATE TABLE',
        'COMMIT;',
        'SET FOREIGN_KEY_CHECKS=1;',
    ];

    public function create(
        string $backupType = 'Manual',
        ?int $userId = null,
    ): DatabaseBackup {
        $backupType = in_array(
            $backupType,
            [
                'Manual',
                'Scheduled',
                'Pre-Restore',
            ],
            true,
        )
            ? $backupType
            : 'Manual';

        $databaseName =
            $this->databaseName();

        $backupNo =
            $this->getNextBackupNo();

        $fileName =
            $backupNo
            .'-'
            .now('Asia/Karachi')
                ->format('Y-m-d-H-i-s')
            .'.sql';

        $filePath =
            'backups/'.$fileName;

        $backup = null;
        $fileWritten = false;

        try {
            $sql =
                $this->generateSqlDump(
                    $databaseName,
                );

            $fileWritten =
                Storage::disk('local')
                    ->put(
                        $filePath,
                        $sql,
                    );

            if (! $fileWritten) {
                throw new RuntimeException(
                    'Backup file storage par write nahi hui.',
                );
            }

            $backup =
                DatabaseBackup::query()
                    ->create([
                        'user_id' =>
                            $userId
                            ?? auth()->id(),

                        'backup_no' =>
                            $backupNo,

                        'database_name' =>
                            $databaseName,

                        'file_name' =>
                            $fileName,

                        'file_path' =>
                            $filePath,

                        'file_size' =>
                            Storage::disk('local')
                                ->size($filePath),

                        'backup_type' =>
                            $backupType,

                        'checksum_sha256' =>
                            null,

                        'verification_status' =>
                            'Pending',

                        'verified_at' =>
                            null,

                        'verification_error' =>
                            null,

                        'drive_status' =>
                            'Pending',

                        'status' =>
                            'Processing',

                        'notes' =>
                            'Database backup verification pending.',
                    ]);

            if (! $this->verify($backup)) {
                throw new RuntimeException(
                    'Backup integrity verification failed: '
                    .(
                        $backup->fresh()
                            ?->verification_error
                        ?: 'Unknown verification error'
                    ),
                );
            }

            $backup->update([
                'status' =>
                    'Completed',

                'notes' =>
                    'Database backup created and verified successfully.',
            ]);

            $this->uploadToGoogleDrive(
                $backup,
            );

            return $backup->fresh();
        } catch (Throwable $exception) {
            if ($backup instanceof DatabaseBackup) {
                $backup->update([
                    'status' =>
                        'Failed',

                    'verification_status' =>
                        $backup->verification_status
                        === 'Verified'
                            ? 'Verified'
                            : 'Failed',

                    'verification_error' =>
                        $backup->verification_error
                        ?: $exception->getMessage(),

                    'notes' =>
                        'Database backup failed: '
                        .$exception->getMessage(),
                ]);
            } elseif ($fileWritten) {
                Storage::disk('local')
                    ->delete($filePath);
            }

            throw $exception;
        }
    }

    public function verify(
        DatabaseBackup $backup,
    ): bool {
        $path =
            (string) $backup->file_path;

        if (! $this->isSafeBackupPath($backup)) {
            return $this->markVerificationFailure(
                $backup,
                'Unsafe or invalid backup file path.',
            );
        }

        $disk =
            Storage::disk('local');

        if (! $disk->exists($path)) {
            return $this->markVerificationFailure(
                $backup,
                'Backup file storage par mojood nahi hai.',
            );
        }

        $contents =
            $disk->get($path);

        $actualSize =
            strlen($contents);

        if ($actualSize < 100) {
            return $this->markVerificationFailure(
                $backup,
                'Backup file empty ya incomplete hai.',
            );
        }

        foreach (
            self::REQUIRED_SQL_MARKERS
            as $marker
        ) {
            if (
                ! str_contains(
                    $contents,
                    $marker,
                )
            ) {
                return $this->markVerificationFailure(
                    $backup,
                    'Required SQL marker missing: '
                    .$marker,
                );
            }
        }

        $actualChecksum =
            hash(
                'sha256',
                $contents,
            );

        $storedChecksum =
            trim(
                (string)
                $backup->checksum_sha256,
            );

        if ($storedChecksum !== '') {
            if (
                ! hash_equals(
                    $storedChecksum,
                    $actualChecksum,
                )
            ) {
                return $this->markVerificationFailure(
                    $backup,
                    'SHA-256 checksum mismatch. File may have been modified.',
                );
            }
        }

        $storedSize =
            (int) $backup->file_size;

        if ($storedSize > 0) {
            if ($storedSize !== $actualSize) {
                return $this->markVerificationFailure(
                    $backup,
                    'Backup file size mismatch.',
                );
            }
        }

        $backup->update([
            'file_size' =>
                $actualSize,

            'checksum_sha256' =>
                $actualChecksum,

            'verification_status' =>
                'Verified',

            'verified_at' =>
                now('Asia/Karachi'),

            'verification_error' =>
                null,

            'status' =>
                'Completed',
        ]);

        return true;
    }

    public function prune(
        ?int $retentionDays = null,
        ?int $minimumBackups = null,
    ): int {
        $retentionDays =
            max(
                1,
                $retentionDays
                ?? (int) config(
                    'backup.retention_days',
                    30,
                ),
            );

        $minimumBackups =
            max(
                1,
                $minimumBackups
                ?? (int) config(
                    'backup.minimum_backups',
                    7,
                ),
            );

        $protectedIds =
            DatabaseBackup::query()
                ->latest('id')
                ->limit($minimumBackups)
                ->pluck('id')
                ->all();

        $query =
            DatabaseBackup::query()
                ->where(
                    'created_at',
                    '<',
                    now('Asia/Karachi')
                        ->subDays(
                            $retentionDays,
                        ),
                );

        if (count($protectedIds) > 0) {
            $query->whereNotIn(
                'id',
                $protectedIds,
            );
        }

        $deleted = 0;

        $query
            ->orderBy('id')
            ->get()
            ->each(
                function (
                    DatabaseBackup $backup,
                ) use (&$deleted): void {
                    $backup->delete();

                    $deleted++;
                },
            );

        return $deleted;
    }

    private function markVerificationFailure(
        DatabaseBackup $backup,
        string $message,
    ): bool {
        $backup->update([
            'verification_status' =>
                'Corrupt',

            'verified_at' =>
                now('Asia/Karachi'),

            'verification_error' =>
                $message,

            'status' =>
                'Failed',
        ]);

        return false;
    }

    private function isSafeBackupPath(
        DatabaseBackup $backup,
    ): bool {
        $path =
            str_replace(
                '\\',
                '/',
                (string) $backup->file_path,
            );

        if (
            ! str_starts_with(
                $path,
                'backups/',
            )
        ) {
            return false;
        }

        if (
            str_contains(
                $path,
                '../',
            )
        ) {
            return false;
        }

        return basename($path)
            === (string) $backup->file_name;
    }

    private function uploadToGoogleDrive(
        DatabaseBackup $backup,
    ): void {
        if (
            ! env(
                'GOOGLE_DRIVE_BACKUP_ENABLED',
                false,
            )
        ) {
            $backup->update([
                'drive_status' =>
                    'Disabled',

                'drive_error' =>
                    null,
            ]);

            return;
        }

        try {
            $uploadedFile =
                app(
                    GoogleDriveBackupService::class,
                )->upload(
                    $backup->file_path,
                    $backup->file_name,
                );

            $backup->update([
                'drive_file_id' =>
                    $uploadedFile['id']
                    ?? null,

                'drive_file_name' =>
                    $uploadedFile['name']
                    ?? null,

                'drive_web_view_link' =>
                    $uploadedFile[
                        'web_view_link'
                    ]
                    ?? null,

                'drive_status' =>
                    'Uploaded',

                'drive_uploaded_at' =>
                    now('Asia/Karachi'),

                'drive_error' =>
                    null,

                'notes' =>
                    'Database backup created, verified and uploaded to Google Drive successfully.',
            ]);
        } catch (Throwable $exception) {
            report($exception);

            $backup->update([
                'drive_status' =>
                    'Failed',

                'drive_error' =>
                    $exception->getMessage(),

                'notes' =>
                    'Local backup is verified, but Google Drive upload failed.',
            ]);
        }
    }

    private function databaseName(): string
    {
        $defaultConnection =
            (string) config(
                'database.default',
                'mysql',
            );

        return (string) (
            config(
                'database.connections.'
                .$defaultConnection
                .'.database',
            )
            ?: config(
                'database.connections.mysql.database',
            )
            ?: 'database'
        );
    }

    private function generateSqlDump(
        ?string $databaseName,
    ): string {
        $tables =
            $this->getTables();

        $sql = '';

        $sql .=
            "-- Karwan E Al Saeed Travel & Tours Database Backup\n";

        $sql .=
            '-- Database: '
            .($databaseName ?: '-')
            ."\n";

        $sql .=
            '-- Created At: '
            .now('Asia/Karachi')
                ->format('Y-m-d H:i:s')
            ."\n\n";

        $sql .=
            "SET FOREIGN_KEY_CHECKS=0;\n";

        $sql .=
            "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";

        $sql .=
            "START TRANSACTION;\n\n";

        foreach ($tables as $table) {
            $sql .=
                $this->dumpTableStructure(
                    $table,
                );

            $sql .=
                $this->dumpTableData(
                    $table,
                );
        }

        $sql .=
            "COMMIT;\n";

        $sql .=
            "SET FOREIGN_KEY_CHECKS=1;\n";

        return $sql;
    }

    private function getTables(): array
    {
        $rows =
            DB::select(
                'SHOW TABLES',
            );

        $tables = [];

        foreach ($rows as $row) {
            $values =
                array_values(
                    (array) $row,
                );

            $tables[] =
                $values[0];
        }

        return $tables;
    }

    private function dumpTableStructure(
        string $table,
    ): string {
        $result =
            DB::select(
                'SHOW CREATE TABLE `'
                .$table
                .'`',
            );

        $createTable =
            (array) $result[0];

        $createSql =
            $createTable[
                'Create Table'
            ]
            ?? array_values(
                $createTable,
            )[1]
            ?? '';

        $sql =
            "\n-- --------------------------------------------------------\n";

        $sql .=
            "-- Table structure for table `$table`\n";

        $sql .=
            "-- --------------------------------------------------------\n\n";

        $sql .=
            "DROP TABLE IF EXISTS `$table`;\n";

        $sql .=
            $createSql.";\n\n";

        return $sql;
    }

    private function dumpTableData(
        string $table,
    ): string {
        $rows =
            DB::table($table)
                ->get();

        if ($rows->isEmpty()) {
            return '';
        }

        $sql =
            "-- Data for table `$table`\n\n";

        foreach ($rows as $row) {
            $rowArray =
                (array) $row;

            $columns =
                array_map(
                    fn (
                        string $column,
                    ): string =>
                        '`'
                        .str_replace(
                            '`',
                            '``',
                            $column,
                        )
                        .'`',

                    array_keys(
                        $rowArray,
                    ),
                );

            $values =
                array_map(
                    fn (
                        mixed $value,
                    ): string =>
                        $this->quoteValue(
                            $value,
                        ),

                    array_values(
                        $rowArray,
                    ),
                );

            $sql .=
                'INSERT INTO `'
                .$table
                .'` ('
                .implode(
                    ', ',
                    $columns,
                )
                .') VALUES ('
                .implode(
                    ', ',
                    $values,
                )
                .");\n";
        }

        $sql .= "\n";

        return $sql;
    }

    private function quoteValue(
        mixed $value,
    ): string {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value
                ? '1'
                : '0';
        }

        return DB::getPdo()
            ->quote(
                (string) $value,
            );
    }

    private function getNextBackupNo(): string
    {
        $lastBackup =
            DatabaseBackup::query()
                ->latest('id')
                ->first();

        $nextNumber =
            $lastBackup
                ? $lastBackup->id + 1
                : 1;

        return 'BK-'
            .str_pad(
                (string) $nextNumber,
                5,
                '0',
                STR_PAD_LEFT,
            );
    }
}