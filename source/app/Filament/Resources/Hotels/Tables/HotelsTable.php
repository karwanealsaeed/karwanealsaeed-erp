<?php

namespace App\Filament\Resources\Hotels\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HotelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('hotel_code')
                    ->label('Hotel Code')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('hotel_name')
                    ->label('Hotel')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('city.name')
                    ->label('City')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('city.country.name')
                    ->label('Country')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('supplier.display_name')
                    ->label('Supplier')
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('star_rating')
                    ->label('Rating')
                    ->formatStateUsing(
                        fn ($state): string =>
                            filled($state)
                                ? ((int) $state).' Star'
                                : '-',
                    )
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(
                        isToggledHiddenByDefault: true,
                    ),
            ])
            ->filters([])
            ->recordActions([
                ViewAction::make()
                    ->label('Details')
                    ->icon('heroicon-o-eye'),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}