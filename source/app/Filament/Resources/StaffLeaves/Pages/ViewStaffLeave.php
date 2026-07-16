<?php

namespace App\Filament\Resources\StaffLeaves\Pages;

use App\Filament\Resources\StaffLeaves\StaffLeaveResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewStaffLeave extends ViewRecord
{
    protected static string $resource = StaffLeaveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
