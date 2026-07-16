<?php

namespace App\Filament\Resources\CustomerFamilyMembers\Pages;

use App\Filament\Resources\CustomerFamilyMembers\CustomerFamilyMemberResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomerFamilyMember extends CreateRecord
{
    protected static string $resource = CustomerFamilyMemberResource::class;
}
