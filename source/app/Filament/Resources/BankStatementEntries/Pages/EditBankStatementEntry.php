<?php

namespace App\Filament\Resources\BankStatementEntries\Pages;

use App\Filament\Resources\BankStatementEntries\BankStatementEntryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditBankStatementEntry extends EditRecord
{
    protected static string $resource = BankStatementEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
