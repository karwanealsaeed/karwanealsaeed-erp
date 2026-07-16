<?php

namespace App\Filament\Resources\AccountHeads\Pages;

use App\Filament\Resources\AccountHeads\AccountHeadResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAccountHead extends ViewRecord
{
    protected static string $resource = AccountHeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
