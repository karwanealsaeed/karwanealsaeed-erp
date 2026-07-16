<?php

namespace App\Filament\Resources\AccountingPeriodLocks\Pages;

use App\Filament\Resources\AccountingPeriodLocks\AccountingPeriodLockResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAccountingPeriodLocks extends ListRecords
{
    protected static string $resource =
        AccountingPeriodLockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(
                    'Create Accounting Period'
                ),
        ];
    }
}