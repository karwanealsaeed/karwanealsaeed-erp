<?php

namespace App\Filament\Resources\UmrahBookings\Pages;

use App\Filament\Resources\UmrahBookings\UmrahBookingResource;
use Filament\Resources\Pages\ViewRecord;

class ViewUmrahBooking extends ViewRecord
{
    protected static string $resource = UmrahBookingResource::class;

    protected string $view = 'filament.resources.shared.record-view';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
