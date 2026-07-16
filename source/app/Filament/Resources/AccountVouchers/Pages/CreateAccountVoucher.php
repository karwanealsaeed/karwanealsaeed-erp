<?php

namespace App\Filament\Resources\AccountVouchers\Pages;

use App\Filament\Resources\AccountVouchers\AccountVoucherResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAccountVoucher extends CreateRecord
{
    protected static string $resource = AccountVoucherResource::class;
}
