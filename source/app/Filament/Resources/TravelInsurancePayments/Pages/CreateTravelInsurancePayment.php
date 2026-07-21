<?php

declare(strict_types=1);

namespace App\Filament\Resources\TravelInsurancePayments\Pages;

use App\Filament\Resources\TravelInsurancePayments\TravelInsurancePaymentResource;
use App\Services\TravelInsurancePrivateFileService;
use Filament\Resources\Pages\CreateRecord;

class CreateTravelInsurancePayment extends CreateRecord
{
    protected static string $resource =
        TravelInsurancePaymentResource::class;

    protected function mutateFormDataBeforeCreate(
        array $data,
    ): array {
        $data['status'] =
            'Pending';

        $data['posting_status'] =
            'Pending';

        $data['account_voucher_id'] =
            null;

        $data['approved_by'] =
            null;

        $data['approved_at'] =
            null;

        return $data;
    }

    protected function afterCreate(): void
    {
        app(
            TravelInsurancePrivateFileService::class,
        )->syncPaymentReceipt(
            $this->record,
        );
    }
}