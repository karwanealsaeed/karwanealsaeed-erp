<?php

namespace App\Filament\Resources\UmrahHotels\Pages;

use App\Filament\Resources\UmrahHotels\UmrahHotelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUmrahHotels extends ListRecords
{
    protected static string $resource = UmrahHotelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->hidden(fn (): bool => ! UmrahHotelResource::canCreate()),
        ];
    }
}