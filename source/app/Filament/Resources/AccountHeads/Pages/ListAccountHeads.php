<?php

namespace App\Filament\Resources\AccountHeads\Pages;

use App\Filament\Resources\AccountHeads\AccountHeadResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAccountHeads extends ListRecords
{
    protected static string $resource = AccountHeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
