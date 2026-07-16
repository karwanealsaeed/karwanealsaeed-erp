<?php

namespace App\Filament\Widgets;

use App\Models\FlightBooking;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentFlightBookings extends TableWidget
{
    protected static ?string $heading = 'Recent Flight Bookings';

    protected int|string|array $columnSpan = 'full';


    public static function canView(): bool
    {
        return auth()->user()?->hasPermission('flight_bookings.view') ?? false;
    }
    public function table(Table $table): Table
    {
        return $table
            ->query(
                FlightBooking::query()->latest()->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('booking_no')
                    ->label('Booking No')
                    ->searchable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),

                Tables\Columns\TextColumn::make('airline.name')
                    ->label('Airline')
                    ->searchable(),

                Tables\Columns\TextColumn::make('fromAirport.name')
                    ->label('From'),

                Tables\Columns\TextColumn::make('toAirport.name')
                    ->label('To'),

                Tables\Columns\TextColumn::make('departure_date')
                    ->label('Departure')
                    ->date('d M Y'),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('PKR', divideBy: 1),

                Tables\Columns\TextColumn::make('balance_amount')
                    ->label('Balance')
                    ->money('PKR', divideBy: 1),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
            ])
            ->paginated(false);
    }
}
