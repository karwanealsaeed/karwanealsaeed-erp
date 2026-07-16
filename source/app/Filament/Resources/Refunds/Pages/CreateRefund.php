<?php

namespace App\Filament\Resources\Refunds\Pages;

use App\Filament\Resources\Refunds\RefundResource;
use App\Models\Refund;
use Filament\Resources\Pages\CreateRecord;

class CreateRefund extends CreateRecord
{
    protected static string $resource = RefundResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $lastRefund = Refund::latest('id')->first();

        $nextNumber = $lastRefund ? $lastRefund->id + 1 : 1;

        $data['refund_no'] = 'RF-' . str_pad((string) $nextNumber, 5, '0', STR_PAD_LEFT);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return RefundResource::getUrl('index');
    }
}
