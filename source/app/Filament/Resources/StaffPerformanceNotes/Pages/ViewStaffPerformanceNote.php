<?php

namespace App\Filament\Resources\StaffPerformanceNotes\Pages;

use App\Filament\Resources\StaffPerformanceNotes\StaffPerformanceNoteResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewStaffPerformanceNote extends ViewRecord
{
    protected static string $resource = StaffPerformanceNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
