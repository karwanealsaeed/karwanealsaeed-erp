<?php

declare(strict_types=1);

namespace App\Filament\Resources\AiSettings\Pages;

use App\Filament\Resources\AiSettings\AiSettingResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAiSetting extends EditRecord
{
    protected static string $resource =
        AiSettingResource::class;

    protected function mutateFormDataBeforeSave(
        array $data,
    ): array {
        $data['key'] =
            'default';

        $data['provider'] =
            'local';

        $data['model'] =
            null;

        $data['external_enabled'] =
            false;

        $data['updated_by'] =
            auth()->id();

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
