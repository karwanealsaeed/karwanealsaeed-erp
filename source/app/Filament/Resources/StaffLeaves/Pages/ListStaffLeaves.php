<?php

namespace App\Filament\Resources\StaffLeaves\Pages;

use App\Filament\Resources\StaffLeaves\StaffLeaveResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStaffLeaves extends ListRecords
{
    protected static string $resource = StaffLeaveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
