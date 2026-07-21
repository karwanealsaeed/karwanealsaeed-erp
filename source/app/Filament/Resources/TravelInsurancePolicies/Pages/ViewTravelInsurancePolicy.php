<?php

declare(strict_types=1);

namespace App\Filament\Resources\TravelInsurancePolicies\Pages;

use App\Filament\Resources\TravelInsurancePolicies\TravelInsurancePolicyResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTravelInsurancePolicy extends ViewRecord
{
    protected static string $resource =
        TravelInsurancePolicyResource::class;

    protected function getHeaderActions(): array
    {
        return [
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