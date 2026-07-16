<?php

namespace App\Filament\Resources\UmrahHotels\Pages;

use App\Filament\Resources\UmrahHotels\UmrahHotelResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUmrahHotel extends EditRecord
{
    protected static string $resource = UmrahHotelResource::class;

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