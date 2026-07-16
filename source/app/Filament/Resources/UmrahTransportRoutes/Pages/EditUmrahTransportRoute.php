<?php

namespace App\Filament\Resources\UmrahTransportRoutes\Pages;

use App\Filament\Resources\UmrahTransportRoutes\UmrahTransportRouteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUmrahTransportRoute extends EditRecord
{
    protected static string $resource = UmrahTransportRouteResource::class;

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