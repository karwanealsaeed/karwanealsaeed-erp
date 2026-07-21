<?php

declare(strict_types=1);

namespace App\Filament\Resources\TravelInsurancePayments\Pages;

use App\Filament\Resources\TravelInsurancePayments\TravelInsurancePaymentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTravelInsurancePayment extends EditRecord
{
    protected static string $resource =
        TravelInsurancePaymentResource::class;

    protected function mutateFormDataBeforeSave(
        array $data,
    ): array {
        unset(
            $data['status'],
            $data['posting_status'],
            $data['account_voucher_id'],
            $data['approved_by'],
            $data['approved_at'],
        );

        return $data;
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