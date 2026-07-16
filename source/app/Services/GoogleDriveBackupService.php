<?php

namespace App\Services;

use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class GoogleDriveBackupService
{
    public function upload(string $localFilePath, string $fileName): array
    {
        if (! env('GOOGLE_DRIVE_BACKUP_ENABLED', false)) {
            throw new RuntimeException('Google Drive backup disabled hai.');
        }

        if (! Storage::disk('local')->exists($localFilePath)) {
            throw new RuntimeException('Local backup file nahi mili: ' . $localFilePath);
        }

        $fullLocalPath = Storage::disk('local')->path($localFilePath);

        $client = app(GoogleDriveOAuthService::class)->getAuthorizedClient();

        $drive = new Drive($client);

        $folderId = trim((string) env('GOOGLE_DRIVE_FOLDER_ID', ''));

        $metadata = [
            'name' => $fileName,
        ];

        if ($folderId !== '') {
            $metadata['parents'] = [$folderId];
        }

        $fileMetadata = new DriveFile($metadata);

        $uploadedFile = $drive->files->create($fileMetadata, [
            'data' => file_get_contents($fullLocalPath),
            'mimeType' => 'application/sql',
            'uploadType' => 'multipart',
            'fields' => 'id,name,webViewLink',
            'supportsAllDrives' => true,
        ]);

        return [
            'id' => $uploadedFile->id,
            'name' => $uploadedFile->name,
            'web_view_link' => $uploadedFile->webViewLink,
        ];
    }
}