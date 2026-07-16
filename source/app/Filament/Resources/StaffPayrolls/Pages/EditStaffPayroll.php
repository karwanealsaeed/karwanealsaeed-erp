<?php

namespace App\Filament\Resources\StaffPayrolls\Pages;

use App\Filament\Resources\StaffPayrolls\StaffPayrollResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditStaffPayroll extends EditRecord
{
    protected static string $resource = StaffPayrollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
