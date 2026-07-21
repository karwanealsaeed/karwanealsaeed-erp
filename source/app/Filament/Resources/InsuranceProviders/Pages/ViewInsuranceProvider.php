<?php

declare(strict_types=1);

namespace App\Filament\Resources\InsuranceProviders\Pages;

use App\Filament\Resources\InsuranceProviders\InsuranceProviderResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewInsuranceProvider extends ViewRecord
{
    protected static string $resource =
        InsuranceProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}