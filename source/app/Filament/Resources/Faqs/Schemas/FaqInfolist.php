<?php

namespace App\Filament\Resources\Faqs\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FaqInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('FAQ Content')
                    ->schema([
                        TextEntry::make('question')
                            ->label('Question — English')
                            ->columnSpanFull(),

                        TextEntry::make('question_ur')
                            ->label('Question — Urdu')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('category')
                            ->label('Category — English')
                            ->badge()
                            ->color('info')
                            ->placeholder('-'),

                        TextEntry::make('category_ur')
                            ->label('Category — Urdu')
                            ->badge()
                            ->color('info')
                            ->placeholder('-'),

                        TextEntry::make('answer')
                            ->label('Answer — English')
                            ->formatStateUsing(
                                fn (mixed $state): string =>
                                    nl2br(e((string) $state)),
                            )
                            ->html()
                            ->columnSpanFull(),

                        TextEntry::make('answer_ur')
                            ->label('Answer — Urdu')
                            ->formatStateUsing(
                                fn (mixed $state): string =>
                                    nl2br(e((string) $state)),
                            )
                            ->html()
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Publication Information')
                    ->schema([
                        TextEntry::make('status')
                            ->badge()
                            ->color(
                                fn (string $state): string => match ($state) {
                                    'Published' => 'success',
                                    'Archived' => 'gray',
                                    default => 'warning',
                                },
                            ),

                        IconEntry::make('is_featured')
                            ->label('Featured')
                            ->boolean(),

                        TextEntry::make('sort_order')
                            ->label('Sort Order')
                            ->numeric(),

                        TextEntry::make('published_at')
                            ->label('Published At')
                            ->dateTime('d M Y, h:i A')
                            ->placeholder('-'),

                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime('d M Y, h:i A'),

                        TextEntry::make('updated_at')
                            ->label('Updated At')
                            ->dateTime('d M Y, h:i A'),
                    ])
                    ->columns(3),
            ]);
    }
}