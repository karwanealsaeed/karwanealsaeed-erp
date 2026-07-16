<?php

namespace App\Filament\Resources\HotelPayments\Pages;

use App\Filament\Resources\HotelPayments\HotelPaymentResource;
use App\Models\HotelPayment;
use Filament\Resources\Pages\CreateRecord;

class CreateHotelPayment extends CreateRecord
{
    protected static string $resource = HotelPaymentResource::class;

    protected function afterCreate(): void
    {
        HotelPayment::recalculateBookingAmounts($this->record->hotel_booking_id);
    }
}
