<?php

namespace App\Filament\Resources\FlightPayments\Pages;

use App\Filament\Resources\FlightPayments\FlightPaymentResource;
use App\Models\FlightPayment;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFlightPayment extends EditRecord
{
    protected static string $resource = FlightPaymentResource::class;

    protected ?int $oldFlightBookingId = null;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->oldFlightBookingId = $this->record->getOriginal('flight_booking_id');

        return $data;
    }

    protected function afterSave(): void
    {
        FlightPayment::recalculateBookingAmounts($this->oldFlightBookingId);
        FlightPayment::recalculateBookingAmounts($this->record->flight_booking_id);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->after(function (): void {
                    FlightPayment::recalculateBookingAmounts($this->record->flight_booking_id);
                }),
        ];
    }
}
