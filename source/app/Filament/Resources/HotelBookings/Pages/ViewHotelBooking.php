<?php

namespace App\Filament\Resources\HotelBookings\Pages;

use App\Filament\Resources\HotelBookings\HotelBookingResource;
use Filament\Resources\Pages\ViewRecord;

class ViewHotelBooking extends ViewRecord
{
    protected static string $resource = HotelBookingResource::class;

    protected string $view = 'filament.resources.shared.record-view';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
