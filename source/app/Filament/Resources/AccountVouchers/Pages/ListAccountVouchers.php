<?php

namespace App\Filament\Resources\AccountVouchers\Pages;

use App\Filament\Resources\AccountVouchers\AccountVoucherResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAccountVouchers extends ListRecords
{
    protected static string $resource = AccountVoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
