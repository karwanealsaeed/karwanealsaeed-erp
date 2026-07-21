<?php

declare(strict_types=1);

namespace App\Filament\Resources\TravelInsurancePayments\Pages;

use App\Filament\Resources\TravelInsurancePayments\TravelInsurancePaymentResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTravelInsurancePayment extends ViewRecord
{
    protected static string $resource =
        TravelInsurancePaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make(
                'downloadPaymentReceipt',
            )
                ->label(
                    'Download Payment Receipt',
                )
                ->icon(
                    'heroicon-o-arrow-down-tray',
                )
                ->color(
                    'success',
                )
                ->url(
                    fn (): string =>
                        route(
                            'travel-insurance-payments.receipt.download',
                            [
                                'travelInsurancePayment' =>
                                    $this->record,
                            ],
                        ),
                )
                ->visible(
                    fn (): bool =>
                        filled(
                            $this->record
                                ->receipt_path,
                        )
                        && (
                            auth()->user()?->hasPermission(
                                'travel_insurance_receipts_download',
                            ) ?? false
                        ),
                ),

            EditAction::make(),
        ];
    }
}