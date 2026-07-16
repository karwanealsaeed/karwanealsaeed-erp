<?php

namespace App\Filament\Resources\FlightPayments\Schemas;

use App\Models\FlightBooking;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class FlightPaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('flight_booking_id')
                    ->label('Booking No')
                    ->relationship('flightBooking', 'booking_no')
                    ->getOptionLabelFromRecordUsing(fn (FlightBooking $record): string => $record->booking_no . ' - ' . ($record->customer?->name ?? '-'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateHydrated(function (?int $state, Set $set): void {
                        self::setBookingInfo($state, $set);
                    })
                    ->afterStateUpdated(function (?int $state, Set $set): void {
                        self::setBookingInfo($state, $set);
                    }),

                TextInput::make('customer_name')
                    ->label('Customer')
                    ->disabled()
                    ->dehydrated(false)
                    ->placeholder('Booking select karen'),

                TextInput::make('booking_balance')
                    ->label('Remaining Balance')
                    ->disabled()
                    ->dehydrated(false)
                    ->prefix('PKR')
                    ->placeholder('0'),

                DatePicker::make('payment_date')
                    ->label('Payment Date')
                    ->native(false)
                    ->default(now())
                    ->required(),

                TextInput::make('amount')
                    ->label('Amount')
                    ->numeric()
                    ->prefix('PKR')
                    ->required(),

                Select::make('payment_method')
                    ->label('Payment Method')
                    ->options([
                        'Cash' => 'Cash',
                        'Bank' => 'Bank',
                        'JazzCash' => 'JazzCash',
                        'EasyPaisa' => 'EasyPaisa',
                        'Credit Card' => 'Credit Card',
                        'Cheque' => 'Cheque',
                    ])
                    ->default('Cash')
                    ->required(),

                TextInput::make('reference_no')
                    ->label('Reference No')
                    ->maxLength(255),

                Textarea::make('notes')
                    ->label('Notes')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }

    private static function setBookingInfo(?int $bookingId, Set $set): void
    {
        if (! $bookingId) {
            $set('customer_name', null);
            $set('booking_balance', null);

            return;
        }

        $booking = FlightBooking::query()
            ->with('customer')
            ->find($bookingId);

        if (! $booking) {
            $set('customer_name', null);
            $set('booking_balance', null);

            return;
        }

        $set('customer_name', $booking->customer?->name ?? '-');
        $set('booking_balance', number_format((float) $booking->balance_amount, 2));
    }
}