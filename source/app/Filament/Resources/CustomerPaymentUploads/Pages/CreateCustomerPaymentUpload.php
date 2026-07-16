<?php

namespace App\Filament\Resources\CustomerPaymentUploads\Pages;

use App\Filament\Resources\CustomerPaymentUploads\CustomerPaymentUploadResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomerPaymentUpload extends CreateRecord
{
    protected static string $resource = CustomerPaymentUploadResource::class;
}
