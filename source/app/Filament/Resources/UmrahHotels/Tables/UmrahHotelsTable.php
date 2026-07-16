<?php

namespace App\Filament\Resources\UmrahHotels\Tables;

use App\Models\UmrahHotel;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UmrahHotelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('city')
                    ->label('City')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('hotel_name')
                    ->label('Hotel')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('similar_text')
                    ->label('Similar')
                    ->placeholder('-'),

                TextColumn::make('distance')
                    ->label('Distance')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('location')
                    ->label('Location')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('meal')
                    ->label('Meal')
                    ->placeholder('-'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Active' ? 'success' : 'gray'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Details'),

                EditAction::make()
                    ->hidden(fn (UmrahHotel $record): bool => ! (
                        (auth()->user()?->isAdmin() ?? false)
                        || (auth()->user()?->hasPermission('umrah_bookings.edit') ?? false)
                    )),

                DeleteAction::make()
                    ->hidden(fn (UmrahHotel $record): bool => ! (
                        (auth()->user()?->isAdmin() ?? false)
                        || (auth()->user()?->hasPermission('umrah_bookings.delete') ?? false)
                    )),
            ])
            ->defaultSort('id', 'desc');
    }
}