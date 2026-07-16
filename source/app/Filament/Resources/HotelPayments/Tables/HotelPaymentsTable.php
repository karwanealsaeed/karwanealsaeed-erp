<?php

namespace App\Filament\Resources\HotelPayments\Tables;

use App\Models\HotelPayment;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HotelPaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('hotelBooking.booking_no')
                    ->label('Booking No')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('hotelBooking.customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('hotelBooking.hotel_name')
                    ->label('Hotel')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('payment_date')
                    ->label('Payment Date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('PKR', divideBy: 1)
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label('Method')
                    ->badge(),

                TextColumn::make('reference_no')
                    ->label('Reference')
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y h:i A')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ViewAction::make()->label('Details')->icon('heroicon-o-eye'),

                EditAction::make(),

                Action::make('print_receipt')
                    ->label('Print Receipt')
                    ->icon('heroicon-o-printer')
                    ->hidden(fn (): bool => ! (auth()->user()?->hasPermission('hotel_payments.print') ?? false))
                    ->url(fn (HotelPayment $record): string => route('payment-receipts.print', [
                        'type' => 'hotel',
                        'payment' => $record->id,
                    ]))
                    ->openUrlInNewTab(),

                DeleteAction::make()
                    ->after(function (HotelPayment $record): void {
                        HotelPayment::recalculateBookingAmounts($record->hotel_booking_id);
                    }),
            ]);
    }
}


