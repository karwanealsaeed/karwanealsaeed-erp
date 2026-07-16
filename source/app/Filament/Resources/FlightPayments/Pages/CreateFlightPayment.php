<?php

namespace App\Filament\Resources\FlightPayments\Pages;

use App\Filament\Resources\FlightPayments\FlightPaymentResource;
use App\Models\FlightPayment;
use Filament\Resources\Pages\CreateRecord;

class CreateFlightPayment extends CreateRecord
{
    protected static string $resource = FlightPaymentResource::class;

    protected function afterCreate(): void
    {
        FlightPayment::recalculateBookingAmounts($this->record->flight_booking_id);
    }
}
