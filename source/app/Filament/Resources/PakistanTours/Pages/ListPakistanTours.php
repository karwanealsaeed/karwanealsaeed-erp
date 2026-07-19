<?php

namespace App\Filament\Resources\PakistanTours\Pages;

use App\Filament\Resources\PakistanTours\PakistanTourResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPakistanTours extends ListRecords
{
    protected static string $resource = PakistanTourResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Pakistan Tour'),
        ];
    }
}