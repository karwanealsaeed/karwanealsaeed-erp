<?php

namespace App\Filament\Resources\UmrahTransportRoutes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UmrahTransportRouteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Transport Information')
                    ->schema([
                        TextInput::make('route_code')
                            ->label('Route Code')
                            ->placeholder('TR-01')
                            ->maxLength(255),

                        TextInput::make('title')
                            ->label('Title')
                            ->placeholder('Jeddah Airport to Makkah to Madinah')
                            ->required()
                            ->maxLength(255),

                        Select::make('transport_type')
                            ->label('Transport Type')
                            ->options([
                                'Sharing' => 'Sharing',
                                'Private' => 'Private',
                                'Group Bus' => 'Group Bus',
                                'Economy Bus' => 'Economy Bus',
                                'Luxury Bus' => 'Luxury Bus',
                            ])
                            ->searchable(),

                        Select::make('vehicle_type')
                            ->label('Vehicle Type')
                            ->options([
                                'Economy Bus' => 'Economy Bus',
                                'Luxury Bus' => 'Luxury Bus',
                                'Hiace' => 'Hiace',
                                'Coaster' => 'Coaster',
                                'Sedan Car' => 'Sedan Car',
                                'GMC' => 'GMC',
                                'Toyota Camry' => 'Toyota Camry',
                                'Private Car' => 'Private Car',
                            ])
                            ->searchable(),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'Active' => 'Active',
                                'Inactive' => 'Inactive',
                            ])
                            ->default('Active')
                            ->required(),
                    ])
                    ->columns(3),

                Section::make('Route Details')
                    ->schema([
                        Textarea::make('route_description')
                            ->label('Route Description')
                            ->placeholder('JED-APT > MAK-H > MED-H > MAK-H > JED-APT')
                            ->rows(4)
                            ->columnSpanFull(),

                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}