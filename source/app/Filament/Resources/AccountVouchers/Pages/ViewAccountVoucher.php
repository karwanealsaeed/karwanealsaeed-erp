<?php

namespace App\Filament\Resources\AccountVouchers\Pages;

use App\Filament\Resources\AccountVouchers\AccountVoucherResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAccountVoucher extends ViewRecord
{
    protected static string $resource = AccountVoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
