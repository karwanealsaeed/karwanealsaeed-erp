<?php

namespace App\Filament\Resources\HotelBookings\Schemas;

use App\Models\City;
use App\Models\Hotel;

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

                Select::make('hotel_id')
                    ->label('Hotel Master')
                    ->options(fn (): array => Hotel::query()
                        ->where('status', 'Active')
                        ->with('city.country')
                        ->orderBy('hotel_name')
                        ->get()
                        ->mapWithKeys(
                            fn (Hotel $hotel): array => [
                                $hotel->id =>
                                    $hotel->hotel_code
                                    .' - '
                                    .$hotel->hotel_name
                                    .' - '
                                    .($hotel->city?->name ?: '-'),
                            ],
                        )
                        ->toArray())
                    ->searchable()
                    ->preload()
                    ->helperText(
                        'Optional. Select karne par Hotel, City aur Country snapshots save ke waqt master se sync honge.',
                    ),

                Select::make('city_id')
                    ->label('City Master')
                    ->options(fn (): array => City::query()
                        ->where('status', 'Active')
                        ->with('country')
                        ->orderBy('name')
                        ->get()
                        ->mapWithKeys(
                            fn (City $city): array => [
                                $city->id =>
                                    $city->name
                                    .' - '
                                    .($city->country?->name ?: '-'),
                            ],
                        )
                        ->toArray())
                    ->searchable()
                    ->preload()
                    ->helperText(
                        'Hotel Master select na ho to optional normalized City choose karein.',
                    ),

                Select::make('country_id')
                    ->label('Country Snapshot')
                    ->relationship('country', 'name')
                    ->searchable()
                    ->preload()
                    ->helperText(
                        'Selected Hotel/City se save ke waqt automatically sync hota hai.',
                    ),

                TextInput::make('city')
                    ->label('City Snapshot')
                    ->maxLength(255)
                    ->helperText(
                        'Legacy/manual booking ke liye available; selected master isay overwrite karega.',
                    ),

                TextInput::make('hotel_name')
                    ->label('Hotel Name Snapshot')
                    ->maxLength(255)
                    ->helperText(
                        'Legacy/manual booking ke liye available; selected Hotel Master isay overwrite karega.',
                    ),

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
