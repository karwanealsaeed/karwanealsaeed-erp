<?php

namespace App\Filament\Resources\PakistanTours\Tables;

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

class PakistanToursTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('cover_image')
                    ->label('Image')
                    ->disk('public')
                    ->square()
                    ->defaultImageUrl(
                        asset('images/logo.png'),
                    ),

                TextColumn::make('tour_no')
                    ->label('Tour No.')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('title')
                    ->searchable([
                        'title',
                        'title_ur',
                        'subtitle',
                        'subtitle_ur',
                    ])
                    ->description(
                        fn ($record): ?string => $record->title_ur,
                    )
                    ->wrap()
                    ->limit(55),

                TextColumn::make('destination')
                    ->searchable()
                    ->sortable()
                    ->description(
                        fn ($record): ?string => $record->province,
                    ),

                TextColumn::make('duration_days')
                    ->label('Duration')
                    ->formatStateUsing(
                        fn ($state, $record): string => filled($state)
                            ? $state.' day(s)'
                                .(
                                    filled($record->duration_nights)
                                        ? ' / '
                                            .$record->duration_nights
                                            .' night(s)'
                                        : ''
                                )
                            : '-',
                    )
                    ->sortable(),

                TextColumn::make('start_date')
                    ->label('Departure')
                    ->date('d M Y')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('price')
                    ->money('PKR', divideBy: 1)
                    ->sortable(),

                TextColumn::make('seats_left')
                    ->label('Seats Left')
                    ->numeric()
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(
                        fn (string $state): string => match ($state) {
                            'Published' => 'success',
                            'Archived' => 'danger',
                            default => 'warning',
                        },
                    )
                    ->sortable(),

                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->numeric()
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

                SelectFilter::make('province')
                    ->options(
                        fn (): array => \App\Models\PakistanTour::query()
                            ->whereNotNull('province')
                            ->where('province', '!=', '')
                            ->distinct()
                            ->orderBy('province')
                            ->pluck('province', 'province')
                            ->all(),
                    )
                    ->searchable(),

                TernaryFilter::make('is_featured')
                    ->label('Featured Tours'),
            ])
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