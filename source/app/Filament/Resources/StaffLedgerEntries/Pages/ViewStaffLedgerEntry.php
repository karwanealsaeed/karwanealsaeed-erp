<?php

namespace App\Filament\Resources\StaffLedgerEntries\Pages;

use App\Filament\Resources\StaffLedgerEntries\StaffLedgerEntryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewStaffLedgerEntry extends ViewRecord
{
    protected static string $resource = StaffLedgerEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
