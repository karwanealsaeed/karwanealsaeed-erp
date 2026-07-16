<?php

namespace App\Filament\Resources\CustomerFamilyMembers\Pages;

use App\Filament\Resources\CustomerFamilyMembers\CustomerFamilyMemberResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCustomerFamilyMembers extends ListRecords
{
    protected static string $resource = CustomerFamilyMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
