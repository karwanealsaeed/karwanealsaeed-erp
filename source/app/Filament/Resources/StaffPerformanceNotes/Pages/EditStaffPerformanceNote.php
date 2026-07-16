<?php

namespace App\Filament\Resources\StaffPerformanceNotes\Pages;

use App\Filament\Resources\StaffPerformanceNotes\StaffPerformanceNoteResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditStaffPerformanceNote extends EditRecord
{
    protected static string $resource = StaffPerformanceNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
