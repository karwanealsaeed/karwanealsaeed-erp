<?php

namespace App\Filament\Resources\LoginActivities\Pages;

use App\Filament\Resources\LoginActivities\LoginActivityResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLoginActivity extends ViewRecord
{
    protected static string $resource = LoginActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
