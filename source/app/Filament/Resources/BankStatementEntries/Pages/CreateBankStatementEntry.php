<?php

namespace App\Filament\Resources\BankStatementEntries\Pages;

use App\Filament\Resources\BankStatementEntries\BankStatementEntryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBankStatementEntry extends CreateRecord
{
    protected static string $resource = BankStatementEntryResource::class;
}
