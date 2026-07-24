<?php

namespace App\Filament\Resources\FlightBookings\Pages;

use App\Filament\Resources\FlightBookings\FlightBookingResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditFlightBooking extends EditRecord
{
    protected static string $resource = FlightBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()->hidden(fn (): bool => ! FlightBookingResource::canDelete($this->record)),
        ];
    }
}
