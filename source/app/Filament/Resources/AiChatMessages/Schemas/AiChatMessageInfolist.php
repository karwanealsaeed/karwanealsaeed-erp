<?php

namespace App\Filament\Resources\AiChatMessages\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AiChatMessageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('AI Chat Message')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('customer.name')
                            ->label('Agent')
                            ->placeholder('Public Visitor'),

                        TextEntry::make('sender')
                            ->badge(),

                        TextEntry::make('language')
                            ->badge(),

                        TextEntry::make('category')
                            ->badge()
                            ->placeholder('-'),

                        IconEntry::make('needs_human_support')
                            ->label('Needs Human Support')
                            ->boolean(),

                        TextEntry::make('created_at')
                            ->dateTime('d M Y h:i A'),

                        TextEntry::make('session_token')
                            ->label('Session')
                            ->copyable()
                            ->columnSpanFull(),

                        TextEntry::make('message')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}