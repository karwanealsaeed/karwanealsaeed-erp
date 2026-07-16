<?php

namespace App\Filament\Resources\StaffPayrolls\Pages;

use App\Filament\Resources\StaffPayrolls\StaffPayrollResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewStaffPayroll extends ViewRecord
{
    protected static string $resource = StaffPayrollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
