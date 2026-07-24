<?php

namespace App\Filament\Resources\CustomerPaymentUploads\Pages;

use App\Filament\Resources\CustomerPaymentUploads\CustomerPaymentUploadResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomerPaymentUpload extends EditRecord
{
    protected static string $resource = CustomerPaymentUploadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->hidden(
                    fn (): bool => ! CustomerPaymentUploadResource::canDelete(
                        $this->record,
                    ),
                ),
        ];
    }
}
