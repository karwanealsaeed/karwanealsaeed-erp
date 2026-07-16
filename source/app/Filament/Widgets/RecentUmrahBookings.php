<?php

namespace App\Filament\Widgets;

use App\Models\UmrahBooking;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentUmrahBookings extends TableWidget
{
    protected static ?string $heading = 'Recent Umrah Bookings';

    protected int|string|array $columnSpan = 'full';


    public static function canView(): bool
    {
        return auth()->user()?->hasPermission('umrah_bookings.view') ?? false;
    }
    public function table(Table $table): Table
    {
        return $table
            ->query(
                UmrahBooking::query()->latest()->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('booking_no')
                    ->label('Booking No')
                    ->searchable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),

                Tables\Columns\TextColumn::make('package_name')
                    ->label('Package')
                    ->searchable(),

                Tables\Columns\TextColumn::make('travel_date')
                    ->label('Travel Date')
                    ->date('d M Y'),

                Tables\Columns\TextColumn::make('no_of_persons')
                    ->label('Persons'),

                Tables\Columns\TextColumn::make('package_amount')
                    ->label('Package Amount')
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
