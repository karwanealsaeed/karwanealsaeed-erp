<?php

namespace App\Filament\Resources\AiChatMessages\Pages;

use App\Filament\Resources\AiChatMessages\AiChatMessageResource;
use Filament\Resources\Pages\ListRecords;

class ListAiChatMessages extends ListRecords
{
    protected static string $resource = AiChatMessageResource::class;
}