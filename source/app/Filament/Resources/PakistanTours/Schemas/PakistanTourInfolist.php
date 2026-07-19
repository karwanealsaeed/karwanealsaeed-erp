<?php

namespace App\Filament\Resources\PakistanTours\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PakistanTourInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Tour Overview')
                    ->schema([
                        ImageEntry::make('cover_image')
                            ->label('Cover Image')
                            ->disk('public')
                            ->defaultImageUrl(
                                asset('images/logo.png'),
                            )
                            ->columnSpanFull(),

                        TextEntry::make('tour_no')
                            ->label('Tour Number'),

                        TextEntry::make('slug')
                            ->placeholder('-'),

                        TextEntry::make('title'),

                        TextEntry::make('title_ur')
                            ->label('Title — Urdu')
                            ->placeholder('-'),

                        TextEntry::make('subtitle')
                            ->placeholder('-'),

                        TextEntry::make('subtitle_ur')
                            ->label('Subtitle — Urdu')
                            ->placeholder('-'),

                        TextEntry::make('destination'),

                        TextEntry::make('province')
                            ->placeholder('-'),

                        TextEntry::make('departure_city')
                            ->label('Departure City')
                            ->placeholder('-'),
                    ])
                    ->columns(2),

                Section::make('Schedule and Pricing')
                    ->schema([
                        TextEntry::make('start_date')
                            ->date('d M Y')
                            ->placeholder('-'),

                        TextEntry::make('end_date')
                            ->date('d M Y')
                            ->placeholder('-'),

                        TextEntry::make('duration_days')
                            ->label('Days')
                            ->numeric()
                            ->placeholder('-'),

                        TextEntry::make('duration_nights')
                            ->label('Nights')
                            ->numeric()
                            ->placeholder('-'),

                        TextEntry::make('group_size')
                            ->numeric()
                            ->placeholder('-'),

                        TextEntry::make('seats_available')
                            ->numeric()
                            ->placeholder('-'),

                        TextEntry::make('seats_left')
                            ->numeric()
                            ->placeholder('-'),

                        TextEntry::make('price')
                            ->money('PKR'),

                        TextEntry::make('price_note')
                            ->placeholder('-'),

                        TextEntry::make('price_note_ur')
                            ->label('Price Note — Urdu')
                            ->placeholder('-'),

                        TextEntry::make('status')
                            ->badge()
                            ->color(
                                fn (string $state): string => match ($state) {
                                    'Published' => 'success',
                                    'Archived' => 'danger',
                                    default => 'warning',
                                },
                            ),

                        IconEntry::make('is_featured')
                            ->label('Featured Tour')
                            ->boolean(),

                        TextEntry::make('sort_order')
                            ->label('Sort Order')
                            ->numeric(),
                    ])
                    ->columns(3),

                Section::make('Descriptions')
                    ->schema([
                        TextEntry::make('short_description')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('short_description_ur')
                            ->label('Short Description — Urdu')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('description')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('description_ur')
                            ->label('Full Description — Urdu')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),

                Section::make('Tour Details')
                    ->schema([
                        TextEntry::make('highlights')
                            ->formatStateUsing(
                                fn ($state): string => self::formatList(
                                    $state,
                                ),
                            )
                            ->placeholder('-'),

                        TextEntry::make('highlights_ur')
                            ->label('Highlights — Urdu')
                            ->formatStateUsing(
                                fn ($state): string => self::formatList(
                                    $state,
                                ),
                            )
                            ->placeholder('-'),

                        TextEntry::make('inclusions')
                            ->formatStateUsing(
                                fn ($state): string => self::formatList(
                                    $state,
                                ),
                            )
                            ->placeholder('-'),

                        TextEntry::make('inclusions_ur')
                            ->label('Inclusions — Urdu')
                            ->formatStateUsing(
                                fn ($state): string => self::formatList(
                                    $state,
                                ),
                            )
                            ->placeholder('-'),

                        TextEntry::make('exclusions')
                            ->formatStateUsing(
                                fn ($state): string => self::formatList(
                                    $state,
                                ),
                            )
                            ->placeholder('-'),

                        TextEntry::make('exclusions_ur')
                            ->label('Exclusions — Urdu')
                            ->formatStateUsing(
                                fn ($state): string => self::formatList(
                                    $state,
                                ),
                            )
                            ->placeholder('-'),
                    ])
                    ->columns(2),

                Section::make('Search Engine Optimization')
                    ->collapsed()
                    ->schema([
                        TextEntry::make('meta_title')
                            ->placeholder('-'),

                        TextEntry::make('meta_title_ur')
                            ->label('Meta Title — Urdu')
                            ->placeholder('-'),

                        TextEntry::make('meta_description')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('meta_description_ur')
                            ->label('Meta Description — Urdu')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Record Information')
                    ->collapsed()
                    ->schema([
                        TextEntry::make('created_at')
                            ->dateTime('d M Y, h:i A')
                            ->placeholder('-'),

                        TextEntry::make('updated_at')
                            ->dateTime('d M Y, h:i A')
                            ->placeholder('-'),
                    ])
                    ->columns(2),
            ]);
    }

    private static function formatList(mixed $state): string
    {
        if (blank($state)) {
            return '-';
        }

        if (is_string($state)) {
            return $state;
        }

        return collect($state)
            ->filter()
            ->map(
                fn ($item): string => '• '.trim((string) $item),
            )
            ->implode(PHP_EOL);
    }
}