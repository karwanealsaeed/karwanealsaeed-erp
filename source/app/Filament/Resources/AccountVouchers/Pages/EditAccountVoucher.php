<?php

namespace App\Filament\Resources\AccountVouchers\Pages;

use App\Filament\Resources\AccountVouchers\AccountVoucherResource;
use App\Services\AccountVoucherPostingValidator;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Validation\ValidationException;

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

        try {
            app(
                AccountVoucherPostingValidator::class
            )->assertCanPost(
                is_array($state['lines'] ?? null)
                    ? $state['lines']
                    : []
            );
        } catch (ValidationException $exception) {
            $message = collect(
                $exception->errors()
            )
                ->flatten()
                ->first();

            $message = is_string($message)
                && $message !== ''
                    ? $message
                    : 'Voucher posting validation failed.';

            Notification::make()
                ->title('Voucher post nahi ho saka')
                ->body($message)
                ->danger()
                ->persistent()
                ->send();

            throw ValidationException::withMessages([
                $this->form->getStatePath().'.lines' => $message,
            ]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->hidden(
                    fn (): bool => ! AccountVoucherResource::canDelete(
                        $this->record,
                    ),
                ),
        ];
    }
}
