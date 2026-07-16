<?php

namespace App\Filament\Resources\FlightBookings\Pages;

use App\Filament\Resources\FlightBookings\FlightBookingResource;
use App\Models\FlightBooking;
use Filament\Resources\Pages\CreateRecord;

class CreateFlightBooking extends CreateRecord
{
    protected static string $resource = FlightBookingResource::class;

    protected function fillForm(): void
    {
        parent::fillForm();

        $next = (FlightBooking::max('id') ?? 0) + 1;

        $this->form->fill([
            'booking_no' => 'FB-' . str_pad($next, 5, '0', STR_PAD_LEFT),
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $next = (FlightBooking::max('id') ?? 0) + 1;

        $data['booking_no'] = 'FB-' . str_pad($next, 5, '0', STR_PAD_LEFT);

        $data['fare'] = $data['fare'] ?? 0;
        $data['tax'] = $data['tax'] ?? 0;
        $data['service_fee'] = $data['service_fee'] ?? 0;
        $data['paid_amount'] = $data['paid_amount'] ?? 0;

        $data['total_amount'] =
            (float) $data['fare'] +
            (float) $data['tax'] +
            (float) $data['service_fee'];

        $data['balance_amount'] =
            max((float) $data['total_amount'] - (float) $data['paid_amount'], 0);

        return $data;
    }
}
