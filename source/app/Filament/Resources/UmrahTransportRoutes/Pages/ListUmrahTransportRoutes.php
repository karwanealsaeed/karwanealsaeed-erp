<?php

namespace App\Filament\Resources\UmrahTransportRoutes\Pages;

use App\Filament\Resources\UmrahTransportRoutes\UmrahTransportRouteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUmrahTransportRoutes extends ListRecords
{
    protected static string $resource = UmrahTransportRouteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->hidden(fn (): bool => ! UmrahTransportRouteResource::canCreate()),
        ];
    }
}