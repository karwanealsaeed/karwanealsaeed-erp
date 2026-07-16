<?php

namespace App\Filament\Resources\HotelBookings\Pages;

use App\Filament\Resources\HotelBookings\HotelBookingResource;
use App\Models\HotelBooking;
use Filament\Resources\Pages\CreateRecord;

class CreateHotelBooking extends CreateRecord
{
    protected static string $resource = HotelBookingResource::class;

    protected function fillForm(): void
    {
        parent::fillForm();

        $next = (HotelBooking::max('id') ?? 0) + 1;

        $this->form->fill([
            'booking_no' => 'HB-' . str_pad($next, 5, '0', STR_PAD_LEFT),
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $next = (HotelBooking::max('id') ?? 0) + 1;

        $data['booking_no'] = 'HB-' . str_pad($next, 5, '0', STR_PAD_LEFT);

        $data['total_amount'] = $data['total_amount'] ?? 0;
        $data['paid_amount'] = $data['paid_amount'] ?? 0;

        $data['balance_amount'] = max(
            (float) $data['total_amount'] - (float) $data['paid_amount'],
            0
        );

        return $data;
    }
}
