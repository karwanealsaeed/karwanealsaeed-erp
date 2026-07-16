<?php

namespace App\Filament\Resources\UmrahBookings\Pages;

use App\Filament\Resources\UmrahBookings\UmrahBookingResource;
use App\Models\UmrahBooking;
use Filament\Resources\Pages\CreateRecord;

class CreateUmrahBooking extends CreateRecord
{
    protected static string $resource = UmrahBookingResource::class;

    protected function fillForm(): void
    {
        parent::fillForm();

        $next = (UmrahBooking::max('id') ?? 0) + 1;

        $this->form->fill([
            'booking_no' => 'UB-' . str_pad($next, 5, '0', STR_PAD_LEFT),
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $next = (UmrahBooking::max('id') ?? 0) + 1;

        $data['booking_no'] = 'UB-' . str_pad($next, 5, '0', STR_PAD_LEFT);

        $data['package_amount'] = $data['package_amount'] ?? 0;
        $data['paid_amount'] = $data['paid_amount'] ?? 0;

        $data['balance_amount'] = max(
            (float) $data['package_amount'] - (float) $data['paid_amount'],
            0
        );

        return $data;
    }
}
