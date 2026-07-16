<?php

namespace App\Filament\Resources\UmrahBookings\Pages;

use App\Filament\Resources\UmrahBookings\UmrahBookingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUmrahBooking extends EditRecord
{
    protected static string $resource = UmrahBookingResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['package_amount'] = $data['package_amount'] ?? 0;
        $data['paid_amount'] = $data['paid_amount'] ?? 0;

        $data['balance_amount'] = max(
            (float) $data['package_amount'] - (float) $data['paid_amount'],
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
