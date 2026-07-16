<?php

namespace App\Filament\Resources\UmrahGroupFlights\Pages;

use App\Filament\Resources\UmrahGroupFlights\UmrahGroupFlightResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUmrahGroupFlight extends EditRecord
{
    protected static string $resource = UmrahGroupFlightResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->hidden(fn (): bool => ! (
                    (auth()->user()?->isAdmin() ?? false)
                    || (auth()->user()?->hasPermission('umrah_bookings.delete') ?? false)
                )),
        ];
    }
}