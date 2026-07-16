<?php

namespace App\Filament\Resources\UmrahBookings\Pages;

use App\Filament\Resources\UmrahBookings\UmrahBookingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUmrahBookings extends ListRecords
{
    protected static string $resource = UmrahBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
