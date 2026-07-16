<?php

namespace App\Filament\Widgets;

use App\Models\UmrahPayment;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentUmrahPayments extends TableWidget
{
    protected static ?string $heading = 'Recent Umrah Payments';

    protected int|string|array $columnSpan = 'full';


    public static function canView(): bool
    {
        return auth()->user()?->hasPermission('umrah_payments.view') ?? false;
    }
    public function table(Table $table): Table
    {
        return $table
            ->query(
                UmrahPayment::query()->latest()->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('umrahBooking.booking_no')
                    ->label('Booking No')
                    ->searchable(),

                Tables\Columns\TextColumn::make('umrahBooking.customer.name')
                    ->label('Customer')
                    ->searchable(),

                Tables\Columns\TextColumn::make('umrahBooking.package_name')
                    ->label('Package')
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
