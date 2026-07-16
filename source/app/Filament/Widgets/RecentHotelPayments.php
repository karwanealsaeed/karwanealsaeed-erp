<?php

namespace App\Filament\Widgets;

use App\Models\HotelPayment;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentHotelPayments extends TableWidget
{
    protected static ?string $heading = 'Recent Hotel Payments';

    protected int|string|array $columnSpan = 'full';


    public static function canView(): bool
    {
        return auth()->user()?->hasPermission('hotel_payments.view') ?? false;
    }
    public function table(Table $table): Table
    {
        return $table
            ->query(
                HotelPayment::query()->latest()->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('hotelBooking.booking_no')
                    ->label('Booking No')
                    ->searchable(),

                Tables\Columns\TextColumn::make('hotelBooking.customer.name')
                    ->label('Customer')
                    ->searchable(),

                Tables\Columns\TextColumn::make('hotelBooking.hotel_name')
                    ->label('Hotel')
                    ->searchable(),

                Tables\Columns\TextColumn::make('hotelBooking.city')
                    ->label('City')
                    ->searchable(),

                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Payment Date')
                    ->date('d M Y'),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('PKR', divideBy: 1),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Method')
                    ->badge(),

                Tables\Columns\TextColumn::make('reference_no')
                    ->label('Reference')
                    ->placeholder('-'),
            ])
            ->paginated(false);
    }
}
