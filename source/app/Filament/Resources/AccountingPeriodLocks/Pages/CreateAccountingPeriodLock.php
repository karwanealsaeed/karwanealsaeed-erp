<?php

namespace App\Filament\Resources\AccountingPeriodLocks\Pages;

use App\Filament\Resources\AccountingPeriodLocks\AccountingPeriodLockResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAccountingPeriodLock extends CreateRecord
{
    protected static string $resource =
        AccountingPeriodLockResource::class;

    protected function mutateFormDataBeforeCreate(
        array $data
    ): array {
        $data['status'] = 'Open';
        $data['created_by'] =
            auth()->id();

        return $data;
    }
}