<?php

namespace App\Filament\Resources\UmrahPayments\Pages;

use App\Filament\Resources\UmrahPayments\UmrahPaymentResource;
use App\Models\UmrahPayment;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUmrahPayment extends EditRecord
{
    protected static string $resource = UmrahPaymentResource::class;

    protected ?int $oldUmrahBookingId = null;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->oldUmrahBookingId = $this->record->getOriginal('umrah_booking_id');

        return $data;
    }

    protected function afterSave(): void
    {
        UmrahPayment::recalculateBookingAmounts($this->oldUmrahBookingId);
        UmrahPayment::recalculateBookingAmounts($this->record->umrah_booking_id);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->after(function (): void {
                    UmrahPayment::recalculateBookingAmounts($this->record->umrah_booking_id);
                }),
        ];
    }
}
