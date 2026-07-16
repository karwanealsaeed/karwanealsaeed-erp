<?php

namespace App\Filament\Resources\FlightPayments\Pages;

use App\Filament\Resources\FlightPayments\FlightPaymentResource;
use Filament\Resources\Pages\ViewRecord;

class ViewFlightPayment extends ViewRecord
{
    protected static string $resource = FlightPaymentResource::class;

    protected string $view = 'filament.resources.shared.record-view';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
