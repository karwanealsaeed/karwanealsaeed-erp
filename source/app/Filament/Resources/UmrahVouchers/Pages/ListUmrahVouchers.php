<?php

namespace App\Filament\Resources\UmrahVouchers\Pages;

use App\Filament\Resources\UmrahVouchers\UmrahVoucherResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUmrahVouchers extends ListRecords
{
    protected static string $resource = UmrahVoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->hidden(fn (): bool => ! UmrahVoucherResource::canCreate()),
        ];
    }
}