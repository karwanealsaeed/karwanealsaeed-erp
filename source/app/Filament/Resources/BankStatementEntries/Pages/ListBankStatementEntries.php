<?php

namespace App\Filament\Resources\BankStatementEntries\Pages;

use App\Filament\Resources\BankStatementEntries\BankStatementEntryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBankStatementEntries extends ListRecords
{
    protected static string $resource = BankStatementEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
