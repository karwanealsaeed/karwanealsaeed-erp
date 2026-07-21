<?php

declare(strict_types=1);

namespace App\Filament\Resources\TravelInsurancePolicies\Pages;

use App\Filament\Resources\TravelInsurancePolicies\TravelInsurancePolicyResource;
use App\Services\TravelInsuranceInvoiceService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewTravelInsurancePolicy extends ViewRecord
{
    protected static string $resource =
        TravelInsurancePolicyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make(
                'createOrSyncInvoice',
            )
                ->label(
                    fn (): string =>
                        filled(
                            $this->record->invoice_id,
                        )
                            ? 'Sync Invoice'
                            : 'Create Invoice',
                )
                ->icon(
                    'heroicon-o-document-currency-dollar',
                )
                ->color(
                    'primary',
                )
                ->requiresConfirmation()
                ->modalHeading(
                    'Create or Synchronize Insurance Invoice',
                )
                ->modalDescription(
                    'Policy customer, amounts, payment balance aur status invoice mein synchronize honge.',
                )
                ->visible(
                    fn (): bool =>
                        auth()->user()?->hasPermission(
                            'travel_insurance_policies_invoice',
                        ) ?? false,
                )
                ->action(
                    function (): void {
                        $invoice =
                            app(
                                TravelInsuranceInvoiceService::class,
                            )->createOrSync(
                                $this->record,
                            );

                        $this->record->refresh();

                        Notification::make()
                            ->title(
                                'Insurance invoice synchronized',
                            )
                            ->body(
                                $invoice->invoice_no,
                            )
                            ->success()
                            ->send();
                    },
                ),

            Action::make(
                'downloadPolicyDocument',
            )
                ->label(
                    'Download Policy Document',
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
                            'travel-insurance-policies.document.download',
                            [
                                'travelInsurancePolicy' =>
                                    $this->record,
                            ],
                        ),
                )
                ->visible(
                    fn (): bool =>
                        filled(
                            $this->record
                                ->policy_document_path,
                        )
                        && (
                            auth()->user()?->hasPermission(
                                'travel_insurance_documents_download',
                            ) ?? false
                        ),
                ),

            EditAction::make(),
        ];
    }
}