<?php

namespace App\Filament\Resources\GalleryItems\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GalleryItemInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Gallery Preview')
                    ->schema([
                        ImageEntry::make('image_path')
                            ->label('Gallery Image')
                            ->disk('public')
                            ->height(320)
                            ->columnSpanFull(),

                        ImageEntry::make('thumbnail_path')
                            ->label('Video Thumbnail')
                            ->disk('public')
                            ->height(220)
                            ->visible(
                                fn ($record): bool =>
                                    $record->media_type === 'Video',
                            )
                            ->columnSpanFull(),
                    ]),

                Section::make('Basic Information')
                    ->schema([
                        TextEntry::make('title')
                            ->label('Title'),

                        TextEntry::make('title_ur')
                            ->label('Title — Urdu')
                            ->placeholder('-'),

                        TextEntry::make('slug')
                            ->badge()
                            ->color('gray'),

                        TextEntry::make('media_type')
                            ->label('Media Type')
                            ->badge()
                            ->color(
                                fn (string $state): string => match ($state) {
                                    'Video' => 'info',
                                    default => 'success',
                                },
                            ),

                        TextEntry::make('category')
                            ->placeholder('-'),

                        TextEntry::make('category_ur')
                            ->label('Category — Urdu')
                            ->placeholder('-'),

                        TextEntry::make('album')
                            ->placeholder('-'),

                        TextEntry::make('album_ur')
                            ->label('Album — Urdu')
                            ->placeholder('-'),
                    ])
                    ->columns(2),

                Section::make('Media Information')
                    ->schema([
                        TextEntry::make('video_url')
                            ->label('Video URL')
                            ->url(
                                fn ($record): ?string =>
                                    $record->video_url,
                                shouldOpenInNewTab: true,
                            )
                            ->placeholder('-')
                            ->visible(
                                fn ($record): bool =>
                                    $record->media_type === 'Video',
                            )
                            ->columnSpanFull(),

                        TextEntry::make('alt_text')
                            ->label('Alt Text')
                            ->placeholder('-'),

                        TextEntry::make('alt_text_ur')
                            ->label('Alt Text — Urdu')
                            ->placeholder('-'),

                        TextEntry::make('caption')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('caption_ur')
                            ->label('Caption — Urdu')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Event Details')
                    ->schema([
                        TextEntry::make('location')
                            ->placeholder('-'),

                        TextEntry::make('location_ur')
                            ->label('Location — Urdu')
                            ->placeholder('-'),

                        TextEntry::make('event_date')
                            ->label('Event Date')
                            ->date('d M Y')
                            ->placeholder('-'),

                        TextEntry::make('photographer')
                            ->placeholder('-'),
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