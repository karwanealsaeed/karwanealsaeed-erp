<?php

namespace App\Filament\Resources\VisaTypes\Pages;

use App\Filament\Resources\VisaTypes\VisaTypeResource;
use Filament\Resources\Pages\ViewRecord;

class ViewVisaType extends ViewRecord
{
    protected static string $resource = VisaTypeResource::class;

    protected string $view = 'filament.resources.shared.record-view';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
