<?php

namespace App\Filament\Resources\UmrahPayments\Pages;

use App\Filament\Resources\UmrahPayments\UmrahPaymentResource;
use App\Models\UmrahPayment;
use Filament\Resources\Pages\CreateRecord;

class CreateUmrahPayment extends CreateRecord
{
    protected static string $resource = UmrahPaymentResource::class;

    protected function afterCreate(): void
    {
        UmrahPayment::recalculateBookingAmounts($this->record->umrah_booking_id);
    }
}
