<?php

namespace App\Filament\Resources\UmrahGroupFlights\Pages;

use App\Filament\Resources\UmrahGroupFlights\UmrahGroupFlightResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUmrahGroupFlights extends ListRecords
{
    protected static string $resource = UmrahGroupFlightResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->hidden(fn (): bool => ! UmrahGroupFlightResource::canCreate()),
        ];
    }
}