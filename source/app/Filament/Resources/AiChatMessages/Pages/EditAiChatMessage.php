<?php

namespace App\Filament\Resources\AiChatMessages\Pages;

use App\Filament\Resources\AiChatMessages\AiChatMessageResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAiChatMessage extends EditRecord
{
    protected static string $resource = AiChatMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
