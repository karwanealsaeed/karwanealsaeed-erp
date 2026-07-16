<?php

namespace App\Filament\Resources\CustomerDocuments\Pages;

use App\Filament\Resources\CustomerDocuments\CustomerDocumentResource;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomerDocument extends ViewRecord
{
    protected static string $resource = CustomerDocumentResource::class;

    protected string $view = 'filament.resources.customer-documents.pages.view-customer-document';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
