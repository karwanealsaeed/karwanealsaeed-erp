<?php

namespace App\Filament\Resources\UmrahPackages\Schemas;

use App\Models\Airport;

use App\Models\UmrahGroupFlight;
use App\Models\UmrahHotel;
use App\Models\UmrahTransportRoute;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class UmrahPackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Package Information')
                    ->schema([
                        TextInput::make('package_no')
                            ->label('Package Code')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto Generate')
                            ->helperText('Save ke baad package code auto generate hoga.'),

                        TextInput::make('title')
                            ->label('Package Title')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('subtitle')
                            ->label('Subtitle')
                            ->maxLength(255),

                        FileUpload::make('package_image')
                            ->label('Package Pic')
                            ->disk('public')
                            ->directory('umrah-packages')
                            ->image()
                            ->imageEditor()
                            ->maxSize(4096),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'Available' => 'Available',
                                'Sold Out' => 'Sold Out',
                                'Inactive' => 'Inactive',
                            ])
                            ->default('Available')
                            ->required(),

                        Toggle::make('is_featured')
                            ->label('Featured')
                            ->default(false),

                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(2),

                Section::make('Group Flight')
                    ->description('Group Code select karte hi airline, route, dates, seats automatic package me attach ho jayenge.')
                    ->schema([
                        Select::make('umrah_group_flight_id')
                            ->label('Group Code / Flight Group')
                            ->options(fn (): array => UmrahGroupFlight::query()
                                ->whereIn('status', ['Available', 'Sold Out'])
                                ->orderByDesc('id')
                                ->get()
                                ->mapWithKeys(fn (UmrahGroupFlight $record): array => [
                                    $record->id => $record->group_code . ' - ' . ($record->route ?: '-') . ' - ' . ($record->airline ?: '-'),
                                ])
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required()
                            ->afterStateUpdated(function (?int $state, Set $set): void {
                                self::setGroupFlightDetails($state, $set);
                            }),

                        TextInput::make('group_code')
                            ->label('Selected Group Code')
                            ->disabled()
                            ->dehydrated(true),

                        Select::make('departure_airport_id')
                            ->label('Departure Airport')
                            ->options(fn (): array => Airport::query()
                                ->where('status', true)
                                ->orderBy('iata_code')
                                ->get()
                                ->mapWithKeys(fn (Airport $record): array => [
                                    $record->id =>
                                        strtoupper((string) $record->iata_code)
                                        . ' — '
                                        . $record->name
                                        . ' ('
                                        . $record->city
                                        . ')',
                                ])
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->disabled()
                            ->dehydrated(true)
                            ->helperText(
                                'Linked group flight se auto attach hota hai. Composite standalone route ko ek airport mein force nahi kiya jata.',
                            ),

                        TextInput::make('airline')
                            ->label('Airline')
                            ->disabled()
                            ->dehydrated(true),

                        TextInput::make('flight_route')
                            ->label('Route')
                            ->disabled()
                            ->dehydrated(true),

                        TextInput::make('duration_nights')
                            ->label('Duration')
                            ->suffix('Night(s)')
                            ->disabled()
                            ->dehydrated(true),

                        TextInput::make('seats_left')
                            ->label('Seats Left')
                            ->disabled()
                            ->dehydrated(true),
                    ])
                    ->columns(3),
                Section::make('Hotels')
                    ->description('Hotels master se select karen, details auto package me save ho jayengi.')
                    ->schema([
                        Select::make('makkah_hotel_id')
                            ->label('Makkah Hotel')
                            ->options(fn (): array => UmrahHotel::query()
                                ->where('city', 'Makkah')
                                ->where('status', 'Active')
                                ->orderBy('hotel_name')
                                ->get()
                                ->mapWithKeys(fn (UmrahHotel $record): array => [
                                    $record->id => $record->display_name,
                                ])
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (?int $state, Set $set): void {
                                self::setHotelDetails($state, $set, 'makkah');
                            }),

                        TextInput::make('makkah_nights')
                            ->label('Makkah Nights')
                            ->numeric()
                            ->suffix('Night(s)'),

                        TextInput::make('makkah_hotel')
                            ->label('Makkah Hotel Name')
                            ->disabled()
                            ->dehydrated(true),

                        TextInput::make('makkah_distance')
                            ->label('Makkah Distance')
                            ->disabled()
                            ->dehydrated(true),

                        TextInput::make('makkah_location')
                            ->label('Makkah Location')
                            ->disabled()
                            ->dehydrated(true),

                        Select::make('madina_hotel_id')
                            ->label('Madinah Hotel')
                            ->options(fn (): array => UmrahHotel::query()
                                ->where('city', 'Madinah')
                                ->where('status', 'Active')
                                ->orderBy('hotel_name')
                                ->get()
                                ->mapWithKeys(fn (UmrahHotel $record): array => [
                                    $record->id => $record->display_name,
                                ])
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (?int $state, Set $set): void {
                                self::setHotelDetails($state, $set, 'madina');
                            }),

                        TextInput::make('madina_nights')
                            ->label('Madinah Nights')
                            ->numeric()
                            ->suffix('Night(s)'),

                        TextInput::make('madina_hotel')
                            ->label('Madinah Hotel Name')
                            ->disabled()
                            ->dehydrated(true),

                        TextInput::make('madina_distance')
                            ->label('Madinah Distance')
                            ->disabled()
                            ->dehydrated(true),

                        TextInput::make('madina_location')
                            ->label('Madinah Location')
                            ->disabled()
                            ->dehydrated(true),
                    ])
                    ->columns(5),

                Section::make('Transport')
                    ->schema([
                        Select::make('umrah_transport_route_id')
                            ->label('Transport Route')
                            ->options(fn (): array => UmrahTransportRoute::query()
                                ->where('status', 'Active')
                                ->orderBy('title')
                                ->get()
                                ->mapWithKeys(fn (UmrahTransportRoute $record): array => [
                                    $record->id => $record->display_name,
                                ])
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (?int $state, Set $set): void {
                                $transport = UmrahTransportRoute::find($state);

                                $set('transport', $transport?->route_description ?: $transport?->title);
                            }),

                        Textarea::make('transport')
                            ->label('Transport Details')
                            ->rows(3)
                            ->disabled()
                            ->dehydrated(true)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Included Services')
                    ->schema([
                        Select::make('included_services')
                            ->label('Included Services')
                            ->multiple()
                            ->options([
                                'Accommodation' => 'Accommodation',
                                'Transport' => 'Transport',
                                'Visa' => 'Visa',
                                'Return Ticket' => 'Return Ticket',
                                'Premium Support' => 'Premium Support',
                            ])
                            ->searchable()
                            ->preload(),

                        TextInput::make('other_included_service')
                            ->label('Other Included Service')
                            ->maxLength(255),

                        Select::make('gifted_services')
                            ->label('Complimentary Gifted Services')
                            ->multiple()
                            ->options([
                                'Taif Ziyarat' => 'Taif Ziyarat',
                                'Umrah from Taif' => 'Umrah from Taif',
                                'Makkah Ziyarat' => 'Makkah Ziyarat',
                                'Madina Ziyarat' => 'Madina Ziyarat',
                            ])
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Price Per Person (PKR)')
                    ->schema([
                        TextInput::make('sharing_price')
                            ->label('Sharing')
                            ->numeric()
                            ->prefix('PKR'),

                        TextInput::make('quad_price')
                            ->label('Quad')
                            ->numeric()
                            ->prefix('PKR'),

                        TextInput::make('triple_price')
                            ->label('Triple')
                            ->numeric()
                            ->prefix('PKR'),

                        TextInput::make('double_price')
                            ->label('Double')
                            ->numeric()
                            ->prefix('PKR'),

                        TextInput::make('child_price')
                            ->label('Child')
                            ->numeric()
                            ->prefix('PKR'),

                        TextInput::make('infant_price')
                            ->label('Infant')
                            ->numeric()
                            ->prefix('PKR'),
                    ])
                    ->columns(3),

                Section::make('Text Details')
                    ->schema([
                        Textarea::make('description')
                            ->label('Package Description')
                            ->rows(4)
                            ->columnSpanFull(),

                        Textarea::make('inclusions')
                            ->label('Extra Inclusions Text')
                            ->rows(4),

                        Textarea::make('exclusions')
                            ->label('Exclusions Text')
                            ->rows(4),
                    ])
                    ->columns(2),
            ]);
    }

    private static function setGroupFlightDetails(?int $groupFlightId, Set $set): void
    {
        $groupFlight = UmrahGroupFlight::find($groupFlightId);

        $set('group_code', $groupFlight?->group_code);
        $set(
            'departure_airport_id',
            $groupFlight?->from_airport_id,
        );
        $set('airline_id', $groupFlight?->airline_id);
        $set('airline', $groupFlight?->airline);
        $set('flight_route', $groupFlight?->route);
        $set('duration_nights', $groupFlight?->duration_nights);
        $set('seats_left', $groupFlight?->seats_left);
    }

    private static function setHotelDetails(?int $hotelId, Set $set, string $type): void
{
    $hotel = UmrahHotel::find($hotelId);

    $prefix = $type === 'makkah' ? 'makkah' : 'madina';

    $set($prefix . '_hotel', $hotel?->hotel_full_name);
    $set($prefix . '_distance', $hotel?->distance_text);
    $set($prefix . '_location', $hotel?->location);
}
}