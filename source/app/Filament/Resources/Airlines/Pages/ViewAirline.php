<?php

namespace App\Filament\Resources\Airlines\Pages;

use App\Filament\Resources\Airlines\AirlineResource;
use Filament\Resources\Pages\ViewRecord;

class ViewAirline extends ViewRecord
{
    protected static string $resource = AirlineResource::class;

    protected string $view = 'filament.resources.shared.record-view';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
