<?php

namespace App\Filament\Resources\CompanySettings\Pages;

use App\Filament\Resources\CompanySettings\CompanySettingResource;
use App\Models\CompanySetting;
use Filament\Resources\Pages\CreateRecord;

class CreateCompanySetting extends CreateRecord
{
    protected static string $resource = CompanySettingResource::class;

    public function mount(): void
    {
        $setting = CompanySetting::current();

        $this->redirect(CompanySettingResource::getUrl('edit', [
            'record' => $setting,
        ]));
    }
}
