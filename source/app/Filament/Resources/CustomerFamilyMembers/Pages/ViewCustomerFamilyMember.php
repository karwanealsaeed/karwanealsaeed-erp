<?php

namespace App\Filament\Resources\CustomerFamilyMembers\Pages;

use App\Filament\Resources\CustomerFamilyMembers\CustomerFamilyMemberResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomerFamilyMember extends ViewRecord
{
    protected static string $resource = CustomerFamilyMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
