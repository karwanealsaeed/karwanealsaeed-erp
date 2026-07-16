<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use App\Models\Invoice;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function fillForm(): void
    {
        parent::fillForm();

        $next = (Invoice::max('id') ?? 0) + 1;

        $this->form->fill([
            'invoice_no' => 'INV-' . str_pad($next, 5, '0', STR_PAD_LEFT),
            'invoice_date' => now(),
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $next = (Invoice::max('id') ?? 0) + 1;

        $data['invoice_no'] = 'INV-' . str_pad($next, 5, '0', STR_PAD_LEFT);

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
}
