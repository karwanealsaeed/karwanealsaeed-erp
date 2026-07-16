<?php

namespace App\Filament\Resources\Airports\Pages;

use App\Filament\Resources\Airports\AirportResource;
use Filament\Resources\Pages\ViewRecord;

class ViewAirport extends ViewRecord
{
    protected static string $resource = AirportResource::class;

    protected string $view = 'filament.resources.shared.record-view';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
