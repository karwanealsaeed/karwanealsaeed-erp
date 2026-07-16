<?php

namespace App\Filament\Resources\UmrahBookings\Schemas;

use App\Models\Customer;
use App\Models\UmrahPackage;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class UmrahBookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Booking Information')
                    ->schema([
                        TextInput::make('booking_no')
                            ->label('Booking No')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto Generate'),

                        Select::make('customer_id')
                            ->label('Customer')
                            ->options(fn (): array => Customer::query()
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'Pending' => 'Pending',
                                'Confirmed' => 'Confirmed',
                                'Completed' => 'Completed',
                                'Cancelled' => 'Cancelled',
                            ])
                            ->default('Confirmed')
                            ->required(),
                    ])
                    ->columns(3),

                Section::make('Umrah Package Sale')
                    ->description('Package select karne se flight, hotel, transport aur seats details auto attach hongi.')
                    ->schema([
                        Select::make('umrah_package_id')
                            ->label('Umrah Package')
                            ->options(fn (): array => UmrahPackage::query()
                                ->with('umrahGroupFlight')
                                ->where('status', 'Available')
                                ->orderByDesc('id')
                                ->get()
                                ->mapWithKeys(fn (UmrahPackage $package): array => [
                                    $package->id => $package->package_no . ' - ' . $package->title . ' - ' . ($package->flight_route ?: '-')
                                        . ' - Seats: ' . $package->seats_left,
                                ])
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required()
                            ->afterStateUpdated(function (?int $state, Set $set, Get $get): void {
                                self::setPackageDetails($state, $set, $get);
                            }),

                        Select::make('package_price_type')
                            ->label('Price Type')
                            ->options([
                                'Sharing' => 'Sharing',
                                'Quad' => 'Quad',
                                'Triple' => 'Triple',
                                'Double' => 'Double',
                                'Child' => 'Child',
                                'Infant' => 'Infant',
                            ])
                            ->default('Sharing')
                            ->live()
                            ->required()
                            ->afterStateUpdated(function (?string $state, Set $set, Get $get): void {
                                self::setPackagePrice((int) $get('umrah_package_id'), $state ?: 'Sharing', $set, $get);
                            }),

                        TextInput::make('seats_count')
                            ->label('Seats / Pax')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->live()
                            ->required()
                            ->afterStateUpdated(function (?string $state, Set $set, Get $get): void {
                                self::calculateBookingAmount($set, $get);
                            }),

                        TextInput::make('package_price_per_person')
                            ->label('Price Per Person')
                            ->numeric()
                            ->prefix('PKR')
                            ->disabled()
                            ->dehydrated(true),

                        TextInput::make('package_amount')
                            ->label('Total Package Amount')
                            ->numeric()
                            ->prefix('PKR')
                            ->disabled()
                            ->dehydrated(true),

                        TextInput::make('paid_amount')
                            ->label('Paid Amount')
                            ->numeric()
                            ->prefix('PKR')
                            ->default(0)
                            ->live()
                            ->afterStateUpdated(function (?string $state, Set $set, Get $get): void {
                                self::calculateBalance($set, $get);
                            }),

                        TextInput::make('balance_amount')
                            ->label('Balance Amount')
                            ->numeric()
                            ->prefix('PKR')
                            ->disabled()
                            ->dehydrated(true),

                        TextInput::make('available_seats')
                            ->label('Available Seats')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('package_name')
                            ->label('Package Name')
                            ->disabled()
                            ->dehydrated(true),

                        TextInput::make('flight_route')
                            ->label('Route')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('airline')
                            ->label('Airline')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(4),

                Section::make('Travel / Hotel Details')
                    ->schema([
                        DatePicker::make('departure_date')
                            ->label('Departure Date')
                            ->native(false),

                        DatePicker::make('return_date')
                            ->label('Return Date')
                            ->native(false),

                        TextInput::make('makkah_hotel')
                            ->label('Makkah Hotel')
                            ->maxLength(255),

                        TextInput::make('madina_hotel')
                            ->label('Madinah Hotel')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('Notes')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private static function setPackageDetails(?int $packageId, Set $set, Get $get): void
    {
        $package = UmrahPackage::query()
            ->with('umrahGroupFlight')
            ->find($packageId);

        if (! $package) {
            return;
        }

        $set('package_name', $package->title);
        $set('departure_date', $package->start_date?->toDateString());
        $set('return_date', $package->end_date?->toDateString());
        $set('makkah_hotel', $package->makkah_hotel);
        $set('madina_hotel', $package->madina_hotel);
        $set('flight_route', $package->flight_route);
        $set('airline', $package->airline);
        $set('available_seats', $package->seats_left);

        self::setPackagePrice($package->id, $get('package_price_type') ?: 'Sharing', $set, $get);
    }

    private static function setPackagePrice(int $packageId, string $priceType, Set $set, Get $get): void
    {
        $package = UmrahPackage::find($packageId);

        if (! $package) {
            return;
        }

        $price = match ($priceType) {
            'Quad' => $package->quad_price,
            'Triple' => $package->triple_price,
            'Double' => $package->double_price,
            'Child' => $package->child_price,
            'Infant' => $package->infant_price,
            default => $package->sharing_price,
        };

        $set('package_price_per_person', (float) ($price ?: 0));

        self::calculateBookingAmount($set, $get);
    }

    private static function calculateBookingAmount(Set $set, Get $get): void
    {
        $seats = max((int) ($get('seats_count') ?: 1), 1);
        $price = (float) ($get('package_price_per_person') ?: 0);

        $set('package_amount', $price * $seats);

        self::calculateBalance($set, $get);
    }

    private static function calculateBalance(Set $set, Get $get): void
    {
        $packageAmount = (float) ($get('package_amount') ?: 0);
        $paidAmount = (float) ($get('paid_amount') ?: 0);

        $set('balance_amount', max($packageAmount - $paidAmount, 0));
    }
}