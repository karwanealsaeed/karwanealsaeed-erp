<?php

namespace App\Filament\Resources\HotelBookings\Pages;

use App\Filament\Resources\HotelBookings\HotelBookingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHotelBookings extends ListRecords
{
    protected static string $resource = HotelBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
