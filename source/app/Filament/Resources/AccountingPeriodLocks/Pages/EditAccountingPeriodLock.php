<?php

namespace App\Filament\Resources\AccountingPeriodLocks\Pages;

use App\Filament\Resources\AccountingPeriodLocks\AccountingPeriodLockResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAccountingPeriodLock extends EditRecord
{
    protected static string $resource =
        AccountingPeriodLockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(
                    fn (): bool =>
                        $this->record->status
                        !== 'Locked'
                ),
        ];
    }
}