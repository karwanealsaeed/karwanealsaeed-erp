<?php

namespace App\Filament\Resources\AiChatMessages\Pages;

use App\Filament\Resources\AiChatMessages\AiChatMessageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAiChatMessage extends ViewRecord
{
    protected static string $resource = AiChatMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}