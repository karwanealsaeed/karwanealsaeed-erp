<?php

namespace App\Filament\Resources\HotelPayments\Pages;

use App\Filament\Resources\HotelPayments\HotelPaymentResource;
use Filament\Resources\Pages\ViewRecord;

class ViewHotelPayment extends ViewRecord
{
    protected static string $resource = HotelPaymentResource::class;

    protected string $view = 'filament.resources.shared.record-view';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
