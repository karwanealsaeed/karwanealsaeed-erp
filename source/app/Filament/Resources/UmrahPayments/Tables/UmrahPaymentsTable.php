<?php

namespace App\Filament\Resources\UmrahPayments\Tables;

use App\Models\UmrahPayment;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UmrahPaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('umrahBooking.booking_no')
                    ->label('Booking No')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('umrahBooking.customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('umrahBooking.package_name')
                    ->label('Package')
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
                    ->hidden(fn (): bool => ! (auth()->user()?->hasPermission('umrah_payments.print') ?? false))
                    ->url(fn (UmrahPayment $record): string => route('payment-receipts.print', [
                        'type' => 'umrah',
                        'payment' => $record->id,
                    ]))
                    ->openUrlInNewTab(),

                DeleteAction::make()
                    ->after(function (UmrahPayment $record): void {
                        UmrahPayment::recalculateBookingAmounts($record->umrah_booking_id);
                    }),
            ]);
    }
}


