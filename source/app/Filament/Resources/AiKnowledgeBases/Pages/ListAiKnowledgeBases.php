<?php

namespace App\Filament\Resources\AiKnowledgeBases\Pages;

use App\Filament\Resources\AiKnowledgeBases\AiKnowledgeBaseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAiKnowledgeBases extends ListRecords
{
    protected static string $resource = AiKnowledgeBaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
