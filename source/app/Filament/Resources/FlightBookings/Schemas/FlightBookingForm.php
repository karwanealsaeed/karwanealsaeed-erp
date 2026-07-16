<?php

namespace App\Filament\Resources\FlightBookings\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class FlightBookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('booking_no')
                    ->label('Booking No')
                    ->disabled()
                    ->dehydrated(),

                Select::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('airline_id')
                    ->label('Airline')
                    ->relationship('airline', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('from_airport_id')
                    ->label('From Airport')
                    ->relationship('fromAirport', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('to_airport_id')
                    ->label('To Airport')
                    ->relationship('toAirport', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('pnr')
                    ->label('PNR'),

                TextInput::make('ticket_no')
                    ->label('Ticket No'),

                DatePicker::make('departure_date')
                    ->label('Departure Date')
                    ->required(),

                DatePicker::make('return_date')
                    ->label('Return Date'),

                Select::make('trip_type')
                    ->label('Trip Type')
                    ->options([
                        'One Way' => 'One Way',
                        'Round Trip' => 'Round Trip',
                    ])
                    ->default('One Way')
                    ->required(),

                Select::make('class')
                    ->label('Class')
                    ->options([
                        'Economy' => 'Economy',
                        'Premium Economy' => 'Premium Economy',
                        'Business' => 'Business',
                        'First' => 'First',
                    ])
                    ->default('Economy')
                    ->required(),

                TextInput::make('fare')
                    ->label('Fare')
                    ->numeric()
                    ->prefix('PKR')
                    ->default(0)
                    ->required()
                    ->live()
                    ->dehydrateStateUsing(fn ($state) => $state ?: 0)
                    ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateAmounts($get, $set)),

                TextInput::make('tax')
                    ->label('Tax')
                    ->numeric()
                    ->prefix('PKR')
                    ->default(0)
                    ->required()
                    ->live()
                    ->dehydrateStateUsing(fn ($state) => $state ?: 0)
                    ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateAmounts($get, $set)),

                TextInput::make('service_fee')
                    ->label('Service Fee')
                    ->numeric()
                    ->prefix('PKR')
                    ->default(0)
                    ->required()
                    ->live()
                    ->dehydrateStateUsing(fn ($state) => $state ?: 0)
                    ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateAmounts($get, $set)),

                TextInput::make('total_amount')
                    ->label('Total Amount')
                    ->numeric()
                    ->prefix('PKR')
                    ->disabled()
                    ->dehydrated()
                    ->default(0),

                TextInput::make('paid_amount')
                    ->label('Paid Amount')
                    ->numeric()
                    ->prefix('PKR')
                    ->default(0)
                    ->required()
                    ->live()
                    ->dehydrateStateUsing(fn ($state) => $state ?: 0)
                    ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateAmounts($get, $set)),

                TextInput::make('balance_amount')
                    ->label('Balance Amount')
                    ->numeric()
                    ->prefix('PKR')
                    ->disabled()
                    ->dehydrated()
                    ->default(0),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'Draft' => 'Draft',
                        'Booked' => 'Booked',
                        'Ticket Issued' => 'Ticket Issued',
                        'Cancelled' => 'Cancelled',
                        'Refunded' => 'Refunded',
                    ])
                    ->default('Draft')
                    ->required(),

                Textarea::make('notes')
                    ->label('Notes')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }

    protected static function calculateAmounts(Get $get, Set $set): void
    {
        $fare = (float) ($get('fare') ?? 0);
        $tax = (float) ($get('tax') ?? 0);
        $serviceFee = (float) ($get('service_fee') ?? 0);
        $paid = (float) ($get('paid_amount') ?? 0);

        $total = $fare + $tax + $serviceFee;

        $set('total_amount', $total);
        $set('balance_amount', max($total - $paid, 0));
    }
}
