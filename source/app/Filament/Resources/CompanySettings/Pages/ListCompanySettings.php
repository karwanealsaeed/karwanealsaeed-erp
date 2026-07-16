<?php

namespace App\Filament\Resources\CompanySettings\Pages;

use App\Filament\Resources\CompanySettings\CompanySettingResource;
use App\Models\CompanySetting;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListCompanySettings extends ListRecords
{
    protected static string $resource = CompanySettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('editCompanySettings')
                ->label('Edit Company Settings')
                ->icon('heroicon-o-pencil-square')
                ->url(fn (): string => CompanySettingResource::getUrl('edit', [
                    'record' => CompanySetting::current(),
                ])),
        ];
    }
}
