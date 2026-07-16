<?php

namespace App\Filament\Resources\VisaApplications\Pages;

use App\Models\VisaApplication;
use App\Filament\Resources\VisaApplications\VisaApplicationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVisaApplication extends CreateRecord
{
    protected static string $resource = VisaApplicationResource::class;

    /**
     * Auto Generate Application Number
     */
    protected function fillForm(): void
    {
        parent::fillForm();

        $last = (VisaApplication::max('id') ?? 0) + 1;

        $this->form->fill([
            'application_no' => 'VA-' . str_pad($last, 5, '0', STR_PAD_LEFT),
        ]);
    }

    /**
     * Before Saving Record
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $last = (VisaApplication::max('id') ?? 0) + 1;

        // Auto Application Number
        $data['application_no'] = 'VA-' . str_pad($last, 5, '0', STR_PAD_LEFT);

        // Prevent NULL Values
        $data['visa_fee'] = $data['visa_fee'] ?? 0;
        $data['service_fee'] = $data['service_fee'] ?? 0;
        $data['paid_amount'] = $data['paid_amount'] ?? 0;

        // Dates
        $data['travel_date'] = $data['travel_date'] ?? null;
        $data['return_date'] = $data['return_date'] ?? null;

        // Notes
        $data['notes'] = $data['notes'] ?? '';

        // Total
        $data['total_amount'] =
            (float) $data['visa_fee'] +
            (float) $data['service_fee'];

        // Balance
        $data['balance_amount'] =
            (float) $data['total_amount'] -
            (float) $data['paid_amount'];

        return $data;
    }
}
