<?php

namespace App\Filament\Resources\Airports\Schemas;

use App\Models\City;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AirportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Select::make('country_id')
                    ->relationship('country', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live(),

                TextInput::make('name')
                    ->label('Airport Name')
                    ->required()
                    ->maxLength(255),

                Select::make('city_id')
                    ->label('City')
                    ->options(fn ($get): array => filled($get('country_id'))
                        ? City::query()
                            ->where('country_id', (int) $get('country_id'))
                            ->where('status', 'Active')
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all()
                        : [])
                    ->searchable()
                    ->preload()
                    ->required()
                    ->helperText('Select country first; only matching active cities are shown'),

                TextInput::make('iata_code')
                    ->label('IATA Code')
                    ->required()
                    ->maxLength(3),

                TextInput::make('icao_code')
                    ->label('ICAO Code')
                    ->maxLength(4),

                Select::make('status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ])
                    ->default(1)
                    ->required(),

            ]);
    }
}
