<?php

namespace App\Filament\Resources\StaffPayrolls\Pages;

use App\Filament\Resources\StaffPayrolls\StaffPayrollResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStaffPayrolls extends ListRecords
{
    protected static string $resource = StaffPayrollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
