<?php

namespace App\Filament\Resources\FlightBookings\Pages;

use App\Filament\Resources\FlightBookings\FlightBookingResource;
use Filament\Resources\Pages\ViewRecord;

class ViewFlightBooking extends ViewRecord
{
    protected static string $resource = FlightBookingResource::class;

    protected string $view = 'filament.resources.shared.record-view';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
