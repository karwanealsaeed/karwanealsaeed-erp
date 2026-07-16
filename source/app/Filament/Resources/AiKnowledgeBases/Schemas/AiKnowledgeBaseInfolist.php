<?php

namespace App\Filament\Resources\AiKnowledgeBases\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AiKnowledgeBaseInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('AI Knowledge')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('title')
                            ->label('Title')
                            ->columnSpan(2),

                        TextEntry::make('category')
                            ->badge()
                            ->placeholder('-'),

                        TextEntry::make('keywords')
                            ->columnSpanFull(),

                        TextEntry::make('priority')
                            ->badge(),

                        TextEntry::make('status')
                            ->badge(),

                        TextEntry::make('creator.name')
                            ->label('Created By')
                            ->placeholder('-'),

                        TextEntry::make('answer_english')
                            ->label('English Answer')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('answer_urdu')
                            ->label('Urdu Answer')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('answer_roman_urdu')
                            ->label('Roman Urdu Answer')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}