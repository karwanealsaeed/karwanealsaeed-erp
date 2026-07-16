<?php

namespace App\Filament\Resources\StaffDocuments\Pages;

use App\Filament\Resources\StaffDocuments\StaffDocumentResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewStaffDocument extends ViewRecord
{
    protected static string $resource = StaffDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
