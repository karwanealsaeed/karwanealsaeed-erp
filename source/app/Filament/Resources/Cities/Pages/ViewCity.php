<?php

namespace App\Filament\Resources\Cities\Pages;

use App\Filament\Resources\Cities\CityResource;
use Filament\Resources\Pages\ViewRecord;

class ViewCity extends ViewRecord
{
    protected static string $resource = CityResource::class;

    protected string $view = 'filament.resources.shared.record-view';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
