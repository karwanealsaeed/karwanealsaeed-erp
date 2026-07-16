<?php

namespace App\Filament\Widgets;

use App\Models\HotelBooking;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentHotelBookings extends TableWidget
{
    protected static ?string $heading = 'Recent Hotel Bookings';

    protected int|string|array $columnSpan = 'full';


    public static function canView(): bool
    {
        return auth()->user()?->hasPermission('hotel_bookings.view') ?? false;
    }
    public function table(Table $table): Table
    {
        return $table
            ->query(
                HotelBooking::query()->latest()->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('booking_no')
                    ->label('Booking No')
                    ->searchable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),

                Tables\Columns\TextColumn::make('country.name')
                    ->label('Country')
                    ->searchable(),

                Tables\Columns\TextColumn::make('city')
                    ->label('City')
                    ->searchable(),

                Tables\Columns\TextColumn::make('hotel_name')
                    ->label('Hotel')
                    ->searchable(),

                Tables\Columns\TextColumn::make('check_in_date')
                    ->label('Check-in')
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
