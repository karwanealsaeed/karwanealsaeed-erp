<?php

namespace App\Filament\Resources\FlightPayments\Pages;

use App\Filament\Resources\FlightPayments\FlightPaymentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFlightPayments extends ListRecords
{
    protected static string $resource = FlightPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
