<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['sub_total'] = $data['sub_total'] ?? 0;
        $data['discount'] = $data['discount'] ?? 0;
        $data['tax'] = $data['tax'] ?? 0;
        $data['paid_amount'] = $data['paid_amount'] ?? 0;

        $data['total_amount'] = max(
            ((float) $data['sub_total'] - (float) $data['discount']) + (float) $data['tax'],
            0
        );

        $data['balance_amount'] = max(
            (float) $data['total_amount'] - (float) $data['paid_amount'],
            0
        );

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
