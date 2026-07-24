<?php

namespace App\Filament\Resources\VisaApplications\Pages;

use App\Filament\Resources\VisaApplications\VisaApplicationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditVisaApplication extends EditRecord
{
    protected static string $resource = VisaApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->hidden(
                    fn (): bool => ! VisaApplicationResource::canDelete(
                        $this->record,
                    ),
                ),
        ];
    }
}
