<?php

namespace App\Filament\Resources\StaffPerformanceNotes\Pages;

use App\Filament\Resources\StaffPerformanceNotes\StaffPerformanceNoteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStaffPerformanceNotes extends ListRecords
{
    protected static string $resource = StaffPerformanceNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
