<?php

declare(strict_types=1);

namespace App\Filament\Resources\AiUsageLogs\Pages;

use App\Filament\Resources\AiUsageLogs\AiUsageLogResource;
use Filament\Resources\Pages\ViewRecord;

class ViewAiUsageLog extends ViewRecord
{
    protected static string $resource =
        AiUsageLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
