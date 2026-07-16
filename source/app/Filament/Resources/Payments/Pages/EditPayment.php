<?php

namespace App\Filament\Resources\Payments\Pages;

use App\Filament\Resources\Payments\PaymentResource;
use App\Models\VisaApplication;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPayment extends EditRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),

            DeleteAction::make()
                ->after(function () {
                    $visa = VisaApplication::find($this->record->visa_application_id);

                    if ($visa) {
                        $paid = $visa->payments()->sum('amount');

                        $visa->update([
                            'paid_amount' => $paid,
                            'balance_amount' => $visa->total_amount - $paid,
                        ]);
                    }
                }),
        ];
    }

    protected function afterSave(): void
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
