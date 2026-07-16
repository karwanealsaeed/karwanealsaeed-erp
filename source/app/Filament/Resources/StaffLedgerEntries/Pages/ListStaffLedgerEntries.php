<?php

namespace App\Filament\Resources\StaffLedgerEntries\Pages;

use App\Filament\Resources\StaffLedgerEntries\StaffLedgerEntryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStaffLedgerEntries extends ListRecords
{
    protected static string $resource = StaffLedgerEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
