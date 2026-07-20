<?php

namespace App\Filament\Resources\BlogPosts\Tables;

use App\Models\BlogPost;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class BlogPostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make(
                    'featured_image_path',
                )
                    ->label('Image')
                    ->disk('public')
                    ->width(82)
                    ->height(54),

                TextColumn::make('title')
                    ->label('Article')
                    ->description(
                        fn (BlogPost $record): ?string =>
                            $record->excerpt,
                    )
                    ->searchable([
                        'title',
                        'title_ur',
                        'excerpt',
                        'excerpt_ur',
                        'content',
                        'content_ur',
                    ])
                    ->sortable()
                    ->limit(80)
                    ->wrap(),

                TextColumn::make('category')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('author_name')
                    ->label('Author')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(
                        fn (string $state): string => match ($state) {
                            'Published' => 'success',
                            'Archived' => 'gray',
                            default => 'warning',
                        },
                    )
                    ->sortable(),

                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('view_count')
                    ->label('Views')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime('d M Y, h:i A')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->numeric()
                    ->sortable()
                    ->toggleable(
                        isToggledHiddenByDefault: true,
                    ),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y, h:i A')
                    ->sortable()
                    ->toggleable(
                        isToggledHiddenByDefault: true,
                    ),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d M Y, h:i A')
                    ->sortable()
                    ->toggleable(
                        isToggledHiddenByDefault: true,
                    ),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'Draft' => 'Draft',
                        'Published' => 'Published',
                        'Archived' => 'Archived',
                    ]),

                SelectFilter::make('category')
                    ->options(
                        fn (): array => BlogPost::query()
                            ->whereNotNull('category')
                            ->where('category', '!=', '')
                            ->distinct()
                            ->orderBy('category')
                            ->pluck('category', 'category')
                            ->all(),
                    )
                    ->searchable(),

                TernaryFilter::make('is_featured')
                    ->label('Featured Articles'),
            ])
            ->defaultSort('published_at', 'desc')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}