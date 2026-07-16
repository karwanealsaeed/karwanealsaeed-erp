<?php

namespace App\Filament\Resources\UmrahPackages\Pages;

use App\Filament\Resources\UmrahPackages\UmrahPackageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUmrahPackage extends EditRecord
{
    protected static string $resource = UmrahPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->hidden(fn (): bool => ! (
                    (auth()->user()?->isAdmin() ?? false)
                    || (auth()->user()?->hasPermission('umrah_bookings.delete') ?? false)
                )),
        ];
    }
}