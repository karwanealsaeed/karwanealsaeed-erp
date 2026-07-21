<?php

declare(strict_types=1);

namespace App\Filament\Resources\TravelInsurancePayments\Pages;

use App\Filament\Resources\TravelInsurancePayments\TravelInsurancePaymentResource;
use App\Services\TravelInsurancePrivateFileService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTravelInsurancePayment extends EditRecord
{
    protected static string $resource =
        TravelInsurancePaymentResource::class;

    private ?string $previousReceiptPath =
        null;

    protected function mutateFormDataBeforeSave(
        array $data,
    ): array {
        $this->previousReceiptPath =
            $this->record->getRawOriginal(
                'receipt_path',
            );

        unset(
            $data['status'],
            $data['posting_status'],
            $data['account_voucher_id'],
            $data['approved_by'],
            $data['approved_at'],
        );

        return $data;
    }

    protected function afterSave(): void
    {
        app(
            TravelInsurancePrivateFileService::class,
        )->syncPaymentReceipt(
            $this->record,
            $this->previousReceiptPath,
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),

            DeleteAction::make()
                ->requiresConfirmation(),
        ];
    }
}