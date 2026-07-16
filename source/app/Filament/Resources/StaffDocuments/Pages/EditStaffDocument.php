<?php

namespace App\Filament\Resources\StaffDocuments\Pages;

use App\Filament\Resources\StaffDocuments\StaffDocumentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditStaffDocument extends EditRecord
{
    protected static string $resource = StaffDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
