<?php

namespace App\Filament\Resources\AiKnowledgeBases\Pages;

use App\Filament\Resources\AiKnowledgeBases\AiKnowledgeBaseResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAiKnowledgeBase extends EditRecord
{
    protected static string $resource = AiKnowledgeBaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
