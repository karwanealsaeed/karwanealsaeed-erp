<?php

declare(strict_types=1);

namespace App\Filament\Resources\TravelInsurancePolicies\Pages;

use App\Filament\Resources\TravelInsurancePolicies\TravelInsurancePolicyResource;
use App\Services\TravelInsurancePrivateFileService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTravelInsurancePolicy extends EditRecord
{
    protected static string $resource =
        TravelInsurancePolicyResource::class;

    private ?string $previousPolicyDocumentPath =
        null;

    protected function mutateFormDataBeforeSave(
        array $data,
    ): array {
        $this->previousPolicyDocumentPath =
            $this->record->getRawOriginal(
                'policy_document_path',
            );

        return $data;
    }

    protected function afterSave(): void
    {
        app(
            TravelInsurancePrivateFileService::class,
        )->syncPolicyDocument(
            $this->record,
            $this->previousPolicyDocumentPath,
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