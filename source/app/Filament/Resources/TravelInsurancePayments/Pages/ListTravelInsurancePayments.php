<?php

declare(strict_types=1);

namespace App\Filament\Resources\TravelInsurancePayments\Pages;

use App\Filament\Resources\TravelInsurancePayments\TravelInsurancePaymentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTravelInsurancePayments extends ListRecords
{
    protected static string $resource =
        TravelInsurancePaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}