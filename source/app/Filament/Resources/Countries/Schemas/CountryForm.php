<?php

namespace App\Filament\Resources\Countries\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

class CountryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Country Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([

                                TextInput::make('name')
                                    ->required(),

                                TextInput::make('code')
                                    ->required()
                                    ->maxLength(5),

                                TextInput::make('iso3')
                                    ->maxLength(5),

                                TextInput::make('currency'),

                                TextInput::make('phone_code'),

                                Select::make('status')
                                    ->options([
                                        1 => 'Active',
                                        0 => 'Inactive',
                                    ])
                                    ->default(1)
                                    ->required(),

                            ]),
                    ]),
            ]);
    }
}
