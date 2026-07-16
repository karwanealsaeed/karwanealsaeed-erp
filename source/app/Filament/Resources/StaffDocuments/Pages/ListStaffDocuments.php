<?php

namespace App\Filament\Resources\StaffDocuments\Pages;

use App\Filament\Resources\StaffDocuments\StaffDocumentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStaffDocuments extends ListRecords
{
    protected static string $resource = StaffDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
