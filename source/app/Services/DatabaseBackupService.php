<?php

namespace App\Services;

use App\Models\DatabaseBackup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class DatabaseBackupService
{
    public function create(): DatabaseBackup
    {
        $databaseName = config('database.connections.mysql.database');
        $backupNo = $this->getNextBackupNo();

        $fileName = $backupNo . '-' . now()->format('Y-m-d-H-i-s') . '.sql';
        $filePath = 'backups/' . $fileName;

        $sql = $this->generateSqlDump($databaseName);

        Storage::disk('local')->put($filePath, $sql);

        $backup = DatabaseBackup::create([
            'user_id' => auth()->id(),
            'backup_no' => $backupNo,
            'database_name' => $databaseName,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_size' => Storage::disk('local')->size($filePath),
            'drive_status' => 'Pending',
            'status' => 'Completed',
            'notes' => 'Database backup created successfully.',
        ]);

        $this->uploadToGoogleDrive($backup);

        return $backup->fresh();
    }

    private function uploadToGoogleDrive(DatabaseBackup $backup): void
    {
        if (! env('GOOGLE_DRIVE_BACKUP_ENABLED', false)) {
            $backup->update([
                'drive_status' => 'Disabled',
                'drive_error' => null,
            ]);

            return;
        }

        try {
            $uploadedFile = app(GoogleDriveBackupService::class)->upload(
                $backup->file_path,
                $backup->file_name
            );

            $backup->update([
                'drive_file_id' => $uploadedFile['id'] ?? null,
                'drive_file_name' => $uploadedFile['name'] ?? null,
                'drive_web_view_link' => $uploadedFile['web_view_link'] ?? null,
                'drive_status' => 'Uploaded',
                'drive_uploaded_at' => now(),
                'drive_error' => null,
                'notes' => 'Database backup created and uploaded to Google Drive successfully.',
            ]);
        } catch (Throwable $exception) {
            report($exception);

            $backup->update([
                'drive_status' => 'Failed',
                'drive_error' => $exception->getMessage(),
                'notes' => 'Local backup created, but Google Drive upload failed.',
            ]);
        }
    }

    private function generateSqlDump(?string $databaseName): string
    {
        $tables = $this->getTables();

        $sql = '';
        $sql .= "-- Karwan E Al Saeed Travel & Tours Database Backup\n";
        $sql .= "-- Database: " . ($databaseName ?: '-') . "\n";
        $sql .= "-- Created At: " . now()->format('Y-m-d H:i:s') . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n";
        $sql .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $sql .= "START TRANSACTION;\n\n";

        foreach ($tables as $table) {
            $sql .= $this->dumpTableStructure($table);
            $sql .= $this->dumpTableData($table);
        }

        $sql .= "COMMIT;\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        return $sql;
    }

    private function getTables(): array
    {
        $rows = DB::select('SHOW TABLES');

        $tables = [];

        foreach ($rows as $row) {
            $values = array_values((array) $row);
            $tables[] = $values[0];
        }

        return $tables;
    }

    private function dumpTableStructure(string $table): string
    {
        $result = DB::select('SHOW CREATE TABLE `' . $table . '`');

        $createTable = (array) $result[0];

        $createSql = $createTable['Create Table'] ?? array_values($createTable)[1] ?? '';

        $sql = "\n-- --------------------------------------------------------\n";
        $sql .= "-- Table structure for table `$table`\n";
        $sql .= "-- --------------------------------------------------------\n\n";
        $sql .= "DROP TABLE IF EXISTS `$table`;\n";
        $sql .= $createSql . ";\n\n";

        return $sql;
    }

    private function dumpTableData(string $table): string
    {
        $rows = DB::table($table)->get();

        if ($rows->isEmpty()) {
            return '';
        }

        $sql = "-- Data for table `$table`\n\n";

        foreach ($rows as $row) {
            $rowArray = (array) $row;

            $columns = array_map(
                fn (string $column): string => '`' . str_replace('`', '``', $column) . '`',
                array_keys($rowArray)
            );

            $values = array_map(
                fn ($value): string => $this->quoteValue($value),
                array_values($rowArray)
            );

            $sql .= 'INSERT INTO `' . $table . '` (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $values) . ");\n";
        }

        $sql .= "\n";

        return $sql;
    }

    private function quoteValue($value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return DB::getPdo()->quote((string) $value);
    }

    private function getNextBackupNo(): string
    {
        $lastBackup = DatabaseBackup::latest('id')->first();

        $nextNumber = $lastBackup ? $lastBackup->id + 1 : 1;

        return 'BK-' . str_pad((string) $nextNumber, 5, '0', STR_PAD_LEFT);
    }
}