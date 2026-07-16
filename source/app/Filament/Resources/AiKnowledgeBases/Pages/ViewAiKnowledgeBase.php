<?php

namespace App\Filament\Resources\AiKnowledgeBases\Pages;

use App\Filament\Resources\AiKnowledgeBases\AiKnowledgeBaseResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAiKnowledgeBase extends ViewRecord
{
    protected static string $resource = AiKnowledgeBaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
