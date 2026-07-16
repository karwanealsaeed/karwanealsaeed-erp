<?php

namespace App\Filament\Resources\HotelPayments\Pages;

use App\Filament\Resources\HotelPayments\HotelPaymentResource;
use App\Models\HotelPayment;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHotelPayment extends EditRecord
{
    protected static string $resource = HotelPaymentResource::class;

    protected ?int $oldHotelBookingId = null;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->oldHotelBookingId = $this->record->getOriginal('hotel_booking_id');

        return $data;
    }

    protected function afterSave(): void
    {
        HotelPayment::recalculateBookingAmounts($this->oldHotelBookingId);
        HotelPayment::recalculateBookingAmounts($this->record->hotel_booking_id);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->after(function (): void {
                    HotelPayment::recalculateBookingAmounts($this->record->hotel_booking_id);
                }),
        ];
    }
}
