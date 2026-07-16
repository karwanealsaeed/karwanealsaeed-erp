<?php

namespace App\Filament\Resources\ActivityLogs\Pages;

use App\Filament\Resources\ActivityLogs\ActivityLogResource;
use Filament\Resources\Pages\ViewRecord;

class ViewActivityLog extends ViewRecord
{
    protected static string $resource = ActivityLogResource::class;

    protected string $view = 'filament.resources.activity-logs.pages.view-activity-log';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
