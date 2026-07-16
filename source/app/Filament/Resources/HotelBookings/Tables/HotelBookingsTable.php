<?php

namespace App\Filament\Resources\HotelBookings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HotelBookingsTable
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

                TextColumn::make('country.name')
                    ->label('Country')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('city')
                    ->label('City')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('hotel_name')
                    ->label('Hotel')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('check_in_date')
                    ->label('Check-in')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('check_out_date')
                    ->label('Check-out')
                    ->date('d M Y')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('no_of_rooms')
                    ->label('Rooms')
                    ->sortable(),

                TextColumn::make('no_of_persons')
                    ->label('Persons')
                    ->sortable(),

                TextColumn::make('room_type')
                    ->label('Room Type')
                    ->placeholder('-')
                    ->searchable(),

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
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d M Y h:i A')
                    ->sortable()
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
