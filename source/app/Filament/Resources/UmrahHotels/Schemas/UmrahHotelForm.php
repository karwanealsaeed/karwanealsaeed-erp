<?php

namespace App\Filament\Resources\UmrahHotels\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UmrahHotelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Hotel Information')
                    ->schema([
                        Select::make('city')
                            ->label('City')
                            ->options([
                                'Makkah' => 'Makkah',
                                'Madinah' => 'Madinah',
                            ])
                            ->required(),

                        TextInput::make('hotel_name')
                            ->label('Hotel Name')
                            ->placeholder('ZAD AL BAIT')
                            ->required()
                            ->maxLength(255),

                        Toggle::make('has_similar')
                            ->label('OR SIMILAR')
                            ->default(false),

                        TextInput::make('distance')
                            ->label('Distance')
                            ->placeholder('700')
                            ->numeric()
                            ->suffix('METER'),

                        TextInput::make('location')
                            ->label('Hotel Location')
                            ->placeholder('IBRAHIM KHALIL ROAD')
                            ->maxLength(255),

                        TextInput::make('meal')
                            ->label('Meal')
                            ->placeholder('Room Only / Breakfast')
                            ->maxLength(255),

                        Select::make('rating')
                            ->label('Rating')
                            ->options([
                                '2 Star' => '2 Star',
                                '3 Star' => '3 Star',
                                '4 Star' => '4 Star',
                                '5 Star' => '5 Star',
                                'Standard' => 'Standard',
                                'Premium' => 'Premium',
                            ])
                            ->searchable(),

                        TextInput::make('contact_no')
                            ->label('Contact No')
                            ->maxLength(255),

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

                Section::make('Notes')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}