<?php

namespace App\Filament\Resources\HotelBookings\Pages;

use App\Filament\Resources\HotelBookings\HotelBookingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHotelBooking extends EditRecord
{
    protected static string $resource = HotelBookingResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['total_amount'] = $data['total_amount'] ?? 0;
        $data['paid_amount'] = $data['paid_amount'] ?? 0;

        $data['balance_amount'] = max(
            (float) $data['total_amount'] - (float) $data['paid_amount'],
            0
        );

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
