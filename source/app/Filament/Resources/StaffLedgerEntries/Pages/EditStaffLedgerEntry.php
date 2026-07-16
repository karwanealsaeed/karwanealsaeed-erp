<?php

namespace App\Filament\Resources\StaffLedgerEntries\Pages;

use App\Filament\Resources\StaffLedgerEntries\StaffLedgerEntryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditStaffLedgerEntry extends EditRecord
{
    protected static string $resource = StaffLedgerEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
