<?php

namespace App\Filament\Resources\StaffLeaves\Pages;

use App\Filament\Resources\StaffLeaves\StaffLeaveResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditStaffLeave extends EditRecord
{
    protected static string $resource = StaffLeaveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
