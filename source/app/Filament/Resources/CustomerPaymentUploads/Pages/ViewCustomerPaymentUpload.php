<?php

namespace App\Filament\Resources\CustomerPaymentUploads\Pages;

use App\Filament\Resources\CustomerPaymentUploads\CustomerPaymentUploadResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomerPaymentUpload extends ViewRecord
{
    protected static string $resource = CustomerPaymentUploadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
