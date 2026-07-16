<?php

namespace App\Filament\Resources\UmrahVouchers\Pages;

use App\Filament\Resources\UmrahVouchers\UmrahVoucherResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUmrahVoucher extends EditRecord
{
    protected static string $resource = UmrahVoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->hidden(fn (): bool => ! (
                    (auth()->user()?->isAdmin() ?? false)
                    || (auth()->user()?->hasPermission('umrah_bookings.delete') ?? false)
                )),
        ];
    }
}