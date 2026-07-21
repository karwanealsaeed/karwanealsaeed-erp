<?php

declare(strict_types=1);

namespace App\Filament\Resources\AiSettings\Pages;

use App\Filament\Resources\AiSettings\AiSettingResource;
use Filament\Resources\Pages\ListRecords;

class ListAiSettings extends ListRecords
{
    protected static string $resource =
        AiSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
