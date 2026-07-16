<?php

namespace App\Filament\Resources\CustomerPaymentUploads\Pages;

use App\Filament\Resources\CustomerPaymentUploads\CustomerPaymentUploadResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCustomerPaymentUploads extends ListRecords
{
    protected static string $resource = CustomerPaymentUploadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
