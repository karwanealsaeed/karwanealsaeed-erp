<?php

namespace App\Filament\Resources\HotelPayments\Pages;

use App\Filament\Resources\HotelPayments\HotelPaymentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHotelPayments extends ListRecords
{
    protected static string $resource = HotelPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
