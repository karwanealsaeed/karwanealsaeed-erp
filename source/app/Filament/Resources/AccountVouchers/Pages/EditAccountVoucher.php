<?php

namespace App\Filament\Resources\AccountVouchers\Pages;

use App\Filament\Resources\AccountVouchers\AccountVoucherResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAccountVoucher extends EditRecord
{
    protected static string $resource = AccountVoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
