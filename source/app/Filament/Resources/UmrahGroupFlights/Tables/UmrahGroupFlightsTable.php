<?php

namespace App\Filament\Resources\UmrahGroupFlights\Tables;

use App\Models\UmrahGroupFlight;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UmrahGroupFlightsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('group_code')
                    ->label('Group Code')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('route')
                    ->label('Route')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('airline')
                    ->label('Airline')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('departure_date')
                    ->label('Departure')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('return_date')
                    ->label('Return')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('duration_nights')
                    ->label('Duration')
                    ->suffix(' Night(s)')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('seats_left')
                    ->label('Seats Left')
                    ->sortable(),

                TextColumn::make('seats_total')
                    ->label('Total Seats')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Available' => 'success',
                        'Sold Out' => 'danger',
                        'Inactive' => 'gray',
                        default => 'gray',
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Details'),

                EditAction::make()
                    ->hidden(fn (UmrahGroupFlight $record): bool => ! (
                        (auth()->user()?->isAdmin() ?? false)
                        || (auth()->user()?->hasPermission('umrah_bookings.edit') ?? false)
                    )),

                DeleteAction::make()
                    ->hidden(fn (UmrahGroupFlight $record): bool => ! (
                        (auth()->user()?->isAdmin() ?? false)
                        || (auth()->user()?->hasPermission('umrah_bookings.delete') ?? false)
                    )),
            ])
            ->defaultSort('id', 'desc');
    }
}