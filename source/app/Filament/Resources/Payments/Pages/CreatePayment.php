<?php

namespace App\Filament\Resources\Payments\Pages;

use App\Filament\Resources\Payments\PaymentResource;
use App\Models\VisaApplication;
use Filament\Resources\Pages\CreateRecord;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    protected function afterCreate(): void
    {
        $visa = VisaApplication::find($this->record->visa_application_id);

        if ($visa) {
            $paid = $visa->payments()->sum('amount');

            $visa->update([
                'paid_amount' => $paid,
                'balance_amount' => $visa->total_amount - $paid,
            ]);
        }
    }
}
