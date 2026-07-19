<?php

namespace App\Filament\Resources\PakistanTours\Pages;

use App\Filament\Resources\PakistanTours\PakistanTourResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPakistanTour extends EditRecord
{
    protected static string $resource = PakistanTourResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}