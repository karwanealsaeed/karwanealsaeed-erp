<?php

namespace App\Filament\Resources\HotelBookings\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class HotelBookingForm
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

                Select::make('country_id')
                    ->label('Country')
                    ->relationship('country', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('city')
                    ->label('City')
                    ->required()
                    ->maxLength(255),

                TextInput::make('hotel_name')
                    ->label('Hotel Name')
                    ->required()
                    ->maxLength(255),

                DatePicker::make('check_in_date')
                    ->label('Check-in Date')
                    ->required(),

                DatePicker::make('check_out_date')
                    ->label('Check-out Date'),

                TextInput::make('no_of_rooms')
                    ->label('No. of Rooms')
                    ->numeric()
                    ->default(1)
                    ->required(),

                TextInput::make('no_of_persons')
                    ->label('No. of Persons')
                    ->numeric()
                    ->default(1)
                    ->required(),

                TextInput::make('room_type')
                    ->label('Room Type')
                    ->maxLength(255),

                TextInput::make('total_amount')
                    ->label('Total Amount')
                    ->numeric()
                    ->prefix('PKR')
                    ->default(0)
                    ->required()
                    ->live()
                    ->dehydrateStateUsing(fn ($state) => $state ?: 0)
                    ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateAmounts($get, $set)),

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
                        'Confirmed' => 'Confirmed',
                        'Cancelled' => 'Cancelled',
                        'Completed' => 'Completed',
                    ])
                    ->default('Draft')
                    ->required(),

                Textarea::make('notes')
                    ->label('Notes')
                    ->rows(4)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    protected static function calculateAmounts(Get $get, Set $set): void
    {
        $totalAmount = (float) ($get('total_amount') ?? 0);
        $paidAmount = (float) ($get('paid_amount') ?? 0);

        $set('balance_amount', max($totalAmount - $paidAmount, 0));
    }
}
