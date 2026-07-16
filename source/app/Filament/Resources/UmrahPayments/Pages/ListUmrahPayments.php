<?php

namespace App\Filament\Resources\UmrahPayments\Pages;

use App\Filament\Resources\UmrahPayments\UmrahPaymentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUmrahPayments extends ListRecords
{
    protected static string $resource = UmrahPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
