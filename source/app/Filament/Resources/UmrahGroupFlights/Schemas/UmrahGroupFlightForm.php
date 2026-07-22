<?php

namespace App\Filament\Resources\UmrahGroupFlights\Schemas;

use App\Models\Airport;

use App\Models\Airline;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class UmrahGroupFlightForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Group Information')
                    ->schema([
                        TextInput::make('group_code')
                            ->label('Group Code')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto Generate')
                            ->helperText('Save ke baad Group Code auto generate hoga.'),

                        TextInput::make('title')
                            ->label('Title')
                            ->placeholder('July Umrah Group')
                            ->maxLength(255),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'Available' => 'Available',
                                'Sold Out' => 'Sold Out',
                                'Inactive' => 'Inactive',
                            ])
                            ->default('Available')
                            ->required(),

                        TextInput::make('seats_total')
                            ->label('Total Seats')
                            ->numeric()
                            ->default(0)
                            ->live()
                            ->afterStateUpdated(function (?string $state, Set $set, Get $get): void {
                                if ((int) ($get('seats_left') ?: 0) <= 0) {
                                    $set('seats_left', (int) $state);
                                }
                            }),

                        TextInput::make('seats_left')
                            ->label('Seats Left')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(5),

                Section::make('Airline / Route')
                    ->schema([
                        Select::make('airline_id')
                            ->label('Airline')
                            ->options(fn (): array => Airline::query()
                                ->where('status', true)
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn (Airline $record): array => [
                                    $record->id => $record->name . ' (' . $record->code . ')',
                                ])
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (?int $state, Set $set): void {
                                $airline = Airline::query()
                                    ->where('id', $state)
                                    ->where('status', true)
                                    ->first();

                                $set('airline_code', $airline?->code);
                            })
                            ->required(),

                        TextInput::make('airline_code')
                            ->label('Airline Code')
                            ->placeholder('Auto from airline master')
                            ->disabled()
                            ->dehydrated(true)
                            ->maxLength(255),

                        Select::make('from_airport_id')
                            ->label('From Airport')
                            ->options(fn (): array => self::airportOptions())
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (?int $state, Set $set, Get $get): void {
                                if (blank($get('return_airport_id'))) {
                                    $set('return_airport_id', $state);
                                }

                                self::updateRoute($get, $set);
                            })
                            ->required(),

                        Select::make('to_airport_id')
                            ->label('To Airport')
                            ->options(fn (): array => self::airportOptions())
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(
                                fn (Get $get, Set $set): mixed =>
                                    self::updateRoute($get, $set),
                            )
                            ->required(),

                        Select::make('return_airport_id')
                            ->label('Return Airport')
                            ->options(fn (): array => self::airportOptions())
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(
                                fn (Get $get, Set $set): mixed =>
                                    self::updateRoute($get, $set),
                            )
                            ->nullable(),

                        TextInput::make('route')
                            ->label('Route')
                            ->placeholder('MUX-JED-MUX')
                            ->disabled()
                            ->dehydrated(true),
                    ])
                    ->columns(3),

                Section::make('Dates / Baggage / Meals')
                    ->description('Dates flight segments se auto fill hongi. Zarurat ho to manually change kar sakte hain.')
                    ->schema([
                        DatePicker::make('departure_date')
                            ->label('Departure Date')
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set): mixed => self::calculateDuration($get, $set)),

                        DatePicker::make('return_date')
                            ->label('Return Date')
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set): mixed => self::calculateDuration($get, $set)),

                        TextInput::make('duration_nights')
                            ->label('Duration')
                            ->suffix('Night(s)')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(true),

                        TextInput::make('total_flights')
                            ->label('Total Flights')
                            ->numeric()
                            ->default(2)
                            ->disabled()
                            ->dehydrated(true),

                        TextInput::make('baggage')
                            ->label('Baggage')
                            ->placeholder('30+7 KG')
                            ->maxLength(255),

                        Select::make('meals')
                            ->label('Meals Just in Flight')
                            ->options([
                                'YES' => 'Yes',
                                'NO' => 'No',
                            ])
                            ->default('YES')
                            ->required(),
                    ])
                    ->columns(3),

                Section::make('Flight Segments')
                    ->description('Default 2 segments hain. 1st From/To dalte hi 2nd auto reverse ho jayega, but changeable hai.')
                    ->schema([
                        Repeater::make('flight_segments')
                            ->label(false)
                            ->schema([
                                TextInput::make('flight_no')
                                    ->label('Flight No')
                                    ->placeholder('PK 763')
                                    ->maxLength(255),

                                TextInput::make('from')
                                    ->label('From')
                                    ->placeholder('MUX')
                                    ->live()
                                    ->afterStateUpdated(function (?string $state, Set $set, Get $get): void {
                                        self::syncSegmentParentFields($get, $set);
                                    })
                                    ->maxLength(255),

                                TextInput::make('to')
                                    ->label('To')
                                    ->placeholder('JED')
                                    ->live()
                                    ->afterStateUpdated(function (?string $state, Set $set, Get $get): void {
                                        self::syncSegmentParentFields($get, $set);
                                    })
                                    ->maxLength(255),

                                DatePicker::make('departure_date')
                                    ->label('Departure Date')
                                    ->native(false)
                                    ->live()
                                    ->afterStateUpdated(function (?string $state, Set $set, Get $get): void {
                                        self::syncSegmentParentFields($get, $set);
                                    }),

                                TimePicker::make('departure_time')
                                    ->label('Departure Time')
                                    ->seconds(false),

                                DatePicker::make('arrival_date')
                                    ->label('Arrival Date')
                                    ->native(false),

                                TimePicker::make('arrival_time')
                                    ->label('Arrival Time')
                                    ->seconds(false),
                            ])
                            ->columns(7)
                            ->reorderable()
                            ->default([
                                [
                                    'flight_no' => null,
                                    'from' => null,
                                    'to' => null,
                                    'departure_date' => null,
                                    'departure_time' => null,
                                    'arrival_date' => null,
                                    'arrival_time' => null,
                                ],
                                [
                                    'flight_no' => null,
                                    'from' => null,
                                    'to' => null,
                                    'departure_date' => null,
                                    'departure_time' => null,
                                    'arrival_date' => null,
                                    'arrival_time' => null,
                                ],
                            ])
                            ->afterStateUpdated(function (mixed $state, Set $set): void {
                                $segments = is_array($state) ? array_values($state) : [];

                                if (isset($segments[0])) {
                                    $firstFrom = strtoupper((string) ($segments[0]['from'] ?? ''));
                                    $firstTo = strtoupper((string) ($segments[0]['to'] ?? ''));

                                    if ($firstFrom !== '' && $firstTo !== '' && isset($segments[1])) {
                                        if (blank($segments[1]['from'] ?? null)) {
                                            $segments[1]['from'] = $firstTo;
                                        }

                                        if (blank($segments[1]['to'] ?? null)) {
                                            $segments[1]['to'] = $firstFrom;
                                        }

                                        $set('flight_segments', $segments);
                                    }

                                    if ($firstFrom !== '') {
                                        $fromAirportId =
                                            self::airportIdFromCode(
                                                $firstFrom,
                                            );

                                        if ($fromAirportId !== null) {
                                            $set(
                                                'from_airport_id',
                                                $fromAirportId,
                                            );

                                            $set(
                                                'return_airport_id',
                                                $fromAirportId,
                                            );
                                        }
                                    }

                                    if ($firstTo !== '') {
                                        $toAirportId =
                                            self::airportIdFromCode(
                                                $firstTo,
                                            );

                                        if ($toAirportId !== null) {
                                            $set(
                                                'to_airport_id',
                                                $toAirportId,
                                            );
                                        }
                                    }

                                    if (
                                        $firstFrom !== ''
                                        && $firstTo !== ''
                                    ) {
                                        $set(
                                            'route',
                                            $firstFrom
                                            . '-'
                                            . $firstTo
                                            . '-'
                                            . $firstFrom,
                                        );
                                    }

                                    if (filled($segments[0]['departure_date'] ?? null)) {
                                        $set('departure_date', $segments[0]['departure_date']);
                                    }
                                }

                                if (count($segments) > 1) {
                                    $lastSegment = $segments[count($segments) - 1];

                                    if (filled($lastSegment['departure_date'] ?? null)) {
                                        $set('return_date', $lastSegment['departure_date']);
                                    } elseif (filled($lastSegment['arrival_date'] ?? null)) {
                                        $set('return_date', $lastSegment['arrival_date']);
                                    }
                                }

                                $set('total_flights', count($segments));
                            })
                            ->columnSpanFull(),
                    ]),

                Section::make('Notes')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private static function updateRoute(Get $get, Set $set): void
    {
        $from =
            self::airportCode(
                $get('from_airport_id'),
            );

        $to =
            self::airportCode(
                $get('to_airport_id'),
            );

        $return =
            self::airportCode(
                $get('return_airport_id'),
            );

        if ($from && $to && $return) {
            $set(
                'route',
                $from . '-' . $to . '-' . $return,
            );

            return;
        }

        $set('route', null);
    }

    private static function airportOptions(): array
    {
        return Airport::query()
            ->where('status', true)
            ->orderBy('iata_code')
            ->get()
            ->mapWithKeys(
                fn (Airport $airport): array => [
                    $airport->id =>
                        strtoupper(
                            (string) $airport->iata_code,
                        )
                        . ' — '
                        . $airport->name
                        . ' ('
                        . $airport->city
                        . ')',
                ],
            )
            ->toArray();
    }

    private static function airportCode(
        mixed $airportId,
    ): string {
        if (blank($airportId)) {
            return '';
        }

        $code =
            Airport::query()
                ->where('id', $airportId)
                ->where('status', true)
                ->value('iata_code');

        return strtoupper(
            trim((string) $code),
        );
    }

    private static function airportIdFromCode(
        string $code,
    ): ?int {
        $normalized =
            strtoupper(
                trim($code),
            );

        if ($normalized === '') {
            return null;
        }

        $id =
            Airport::query()
                ->where('status', true)
                ->whereRaw(
                    'UPPER(iata_code) = ?',
                    [$normalized],
                )
                ->value('id');

        return $id === null
            ? null
            : (int) $id;
    }

    private static function calculateDuration(Get $get, Set $set): void
    {
        $departureDate = $get('departure_date');
        $returnDate = $get('return_date');

        if (! $departureDate || ! $returnDate) {
            return;
        }

        $departure = Carbon::parse($departureDate)->startOfDay();
        $return = Carbon::parse($returnDate)->startOfDay();

        $set('duration_nights', max($departure->diffInDays($return), 0));
    }

    private static function syncSegmentParentFields(Get $get, Set $set): void
    {
        $segments = $get('../../flight_segments');

        if (! is_array($segments)) {
            return;
        }

        $segments = array_values($segments);

        if (! isset($segments[0])) {
            return;
        }

        $from = strtoupper((string) ($segments[0]['from'] ?? ''));
        $to = strtoupper((string) ($segments[0]['to'] ?? ''));

        if ($from !== '') {
            $fromAirportId =
                self::airportIdFromCode($from);

            if ($fromAirportId !== null) {
                $set(
                    '../../from_airport_id',
                    $fromAirportId,
                );

                $set(
                    '../../return_airport_id',
                    $fromAirportId,
                );
            }
        }

        if ($to !== '') {
            $toAirportId =
                self::airportIdFromCode($to);

            if ($toAirportId !== null) {
                $set(
                    '../../to_airport_id',
                    $toAirportId,
                );
            }
        }

        if ($from !== '' && $to !== '') {
            $set(
                '../../route',
                $from . '-' . $to . '-' . $from,
            );
        }

        if (isset($segments[1])) {
            if ($to !== '' && blank($segments[1]['from'] ?? null)) {
                $segments[1]['from'] = $to;
            }

            if ($from !== '' && blank($segments[1]['to'] ?? null)) {
                $segments[1]['to'] = $from;
            }

            $set('../../flight_segments', $segments);
        }
    }
}