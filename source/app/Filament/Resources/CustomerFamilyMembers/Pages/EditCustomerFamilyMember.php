<?php

namespace App\Filament\Resources\CustomerFamilyMembers\Pages;

use App\Filament\Resources\CustomerFamilyMembers\CustomerFamilyMemberResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomerFamilyMember extends EditRecord
{
    protected static string $resource = CustomerFamilyMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->hidden(
                    fn (): bool => ! CustomerFamilyMemberResource::canDelete(
                        $this->record,
                    ),
                ),
        ];
    }
}
