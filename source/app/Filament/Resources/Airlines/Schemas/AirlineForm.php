<?php

namespace App\Filament\Resources\Airlines\Schemas;

use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AirlineForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Airline Information')
                    ->schema([

                        Grid::make(2)
                            ->schema([

                                TextInput::make('name')
                                    ->label('Airline Name')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('code')
                                    ->label('Airline Code')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(10),

                                TextInput::make('country')
                                    ->default('Pakistan'),

                                TextInput::make('phone'),

                                TextInput::make('email')
                                    ->email(),

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
