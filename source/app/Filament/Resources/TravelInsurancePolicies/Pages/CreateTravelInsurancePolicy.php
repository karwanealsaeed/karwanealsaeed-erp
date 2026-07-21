<?php

declare(strict_types=1);

namespace App\Filament\Resources\TravelInsurancePolicies\Pages;

use App\Filament\Resources\TravelInsurancePolicies\TravelInsurancePolicyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTravelInsurancePolicy extends CreateRecord
{
    protected static string $resource =
        TravelInsurancePolicyResource::class;
}