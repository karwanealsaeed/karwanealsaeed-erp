<?php

namespace App\Filament\Resources\AiChatMessages;

use App\Filament\Resources\AiChatMessages\Pages\ListAiChatMessages;
use App\Filament\Resources\AiChatMessages\Pages\ViewAiChatMessage;
use App\Filament\Resources\AiChatMessages\Schemas\AiChatMessageInfolist;
use App\Filament\Resources\AiChatMessages\Tables\AiChatMessagesTable;
use App\Models\AiChatMessage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class AiChatMessageResource extends Resource
{
    protected static ?string $model = AiChatMessage::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'AI Chat Logs';

    protected static ?string $modelLabel = 'AI Chat Message';

    protected static ?string $pluralModelLabel = 'AI Chat Logs';

    protected static ?int $navigationSort = 91;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return AiChatMessageInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AiChatMessagesTable::configure($table);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = AiChatMessage::query()
            ->where('needs_human_support', true)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAiChatMessages::route('/'),
            'view' => ViewAiChatMessage::route('/{record}'),
        ];
    }
}