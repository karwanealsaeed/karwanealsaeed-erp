<?php

namespace App\Filament\Resources\StaffAttendances\Pages;

use App\Filament\Resources\StaffAttendances\StaffAttendanceResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewStaffAttendance extends ViewRecord
{
    protected static string $resource = StaffAttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
