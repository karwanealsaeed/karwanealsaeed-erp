<?php

namespace App\Filament\Resources\FlightBookings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FlightBookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('booking_no')
                    ->label('Booking No')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('airline.name')
                    ->label('Airline')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('fromAirport.name')
                    ->label('From')
                    ->searchable(),

                TextColumn::make('toAirport.name')
                    ->label('To')
                    ->searchable(),

                TextColumn::make('pnr')
                    ->label('PNR')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('ticket_no')
                    ->label('Ticket No')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('departure_date')
                    ->label('Departure')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('trip_type')
                    ->label('Trip')
                    ->badge(),

                TextColumn::make('class')
                    ->badge(),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('PKR', divideBy: 1)
                    ->sortable(),

                TextColumn::make('paid_amount')
                    ->label('Paid')
                    ->money('PKR', divideBy: 1)
                    ->sortable(),

                TextColumn::make('balance_amount')
                    ->label('Balance')
                    ->money('PKR', divideBy: 1)
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y h:i A')
                    ->toggleable(isToggledHiddenByDefault: true),
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
