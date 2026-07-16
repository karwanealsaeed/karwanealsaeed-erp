<?php

namespace App\Filament\Resources\DatabaseBackups\Pages;

use App\Filament\Resources\DatabaseBackups\DatabaseBackupResource;
use Filament\Resources\Pages\ViewRecord;

class ViewDatabaseBackup extends ViewRecord
{
    protected static string $resource = DatabaseBackupResource::class;

    protected string $view = 'filament.resources.shared.record-view';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
