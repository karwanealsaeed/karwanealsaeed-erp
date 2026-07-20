<?php

namespace App\Filament\Resources\BlogPosts\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BlogPostInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Article Information')
                    ->schema([
                        ImageEntry::make(
                            'featured_image_path',
                        )
                            ->label('Featured Image')
                            ->disk('public')
                            ->height(260)
                            ->columnSpanFull(),

                        TextEntry::make('title')
                            ->label('Title — English')
                            ->columnSpanFull(),

                        TextEntry::make('title_ur')
                            ->label('Title — Urdu')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('slug')
                            ->copyable()
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

                        TextEntry::make('author_name')
                            ->label('Author')
                            ->placeholder('-'),

                        TextEntry::make('tags')
                            ->formatStateUsing(
                                fn (mixed $state): string =>
                                    is_array($state)
                                        && count($state) > 0
                                            ? implode(', ', $state)
                                            : '-',
                            ),

                        TextEntry::make('excerpt')
                            ->label('Excerpt — English')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('excerpt_ur')
                            ->label('Excerpt — Urdu')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Article Content')
                    ->schema([
                        TextEntry::make('content')
                            ->label('Content — English')
                            ->html()
                            ->columnSpanFull(),

                        TextEntry::make('content_ur')
                            ->label('Content — Urdu')
                            ->html()
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),

                Section::make('SEO Information')
                    ->collapsed()
                    ->schema([
                        TextEntry::make('seo_title')
                            ->label('SEO Title — English')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('seo_title_ur')
                            ->label('SEO Title — Urdu')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('seo_description')
                            ->label(
                                'SEO Description — English',
                            )
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make(
                            'seo_description_ur',
                        )
                            ->label(
                                'SEO Description — Urdu',
                            )
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),

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

                        TextEntry::make('view_count')
                            ->label('Views')
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