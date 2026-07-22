<?php

namespace App\Filament\Resources\AccountVouchers\Pages;

use App\Filament\Resources\AccountVouchers\AccountVoucherResource;
use App\Services\AccountVoucherPostingValidator;
use Illuminate\Contracts\Support\Arrayable;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAccountVoucher extends EditRecord
{
    protected static string $resource = AccountVoucherResource::class;

    protected function beforeSave(): void
    {
        $state = $this->form->getRawState();

        if ($state instanceof Arrayable) {
            $state = $state->toArray();
        }

        if (! is_array($state)) {
            $state = [];
        }

        if (($state['status'] ?? null) !== 'Posted') {
            return;
        }

        app(
            AccountVoucherPostingValidator::class
        )->assertCanPost(
            is_array($state['lines'] ?? null)
                ? $state['lines']
                : []
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
