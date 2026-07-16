<?php

namespace App\Filament\Resources\AccountHeads\Pages;

use App\Filament\Resources\AccountHeads\AccountHeadResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAccountHead extends EditRecord
{
    protected static string $resource = AccountHeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
