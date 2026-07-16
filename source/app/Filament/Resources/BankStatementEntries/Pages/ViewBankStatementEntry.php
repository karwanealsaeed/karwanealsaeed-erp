<?php

namespace App\Filament\Resources\BankStatementEntries\Pages;

use App\Filament\Resources\BankStatementEntries\BankStatementEntryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBankStatementEntry extends ViewRecord
{
    protected static string $resource = BankStatementEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
