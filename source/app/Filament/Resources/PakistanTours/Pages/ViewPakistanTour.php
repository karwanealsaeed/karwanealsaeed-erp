<?php

namespace App\Filament\Resources\PakistanTours\Pages;

use App\Filament\Resources\PakistanTours\PakistanTourResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPakistanTour extends ViewRecord
{
    protected static string $resource = PakistanTourResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}