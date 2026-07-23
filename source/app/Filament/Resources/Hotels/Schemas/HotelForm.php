<?php

namespace App\Filament\Resources\Hotels\Schemas;

use App\Models\City;
use App\Models\Supplier;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HotelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Hotel Master')
                    ->description(
                        'General hotel master. Umrah Hotels remain a separate package-specific master.',
                    )
                    ->columns(3)
                    ->schema([
                        TextInput::make('hotel_code')
                            ->label('Hotel Code')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto-generated as HTL-000001'),

                        TextInput::make('hotel_name')
                            ->label('Hotel Name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        Select::make('city_id')
                            ->label('City')
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
                            ->required(),

                        Select::make('supplier_id')
                            ->label('Supplier')
                            ->options(fn (): array => Supplier::query()
                                ->where('status', 'Active')
                                ->orderBy('company_name')
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(
                                    fn (Supplier $supplier): array => [
                                        $supplier->id =>
                                            $supplier->display_name,
                                    ],
                                )
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->helperText(
                                'Optional hotel supplier/vendor link.',
                            ),

                        Select::make('star_rating')
                            ->label('Star Rating')
                            ->options([
                                1 => '1 Star',
                                2 => '2 Star',
                                3 => '3 Star',
                                4 => '4 Star',
                                5 => '5 Star',
                            ])
                            ->native(false),

                        Select::make('status')
                            ->options([
                                'Active' => 'Active',
                                'Inactive' => 'Inactive',
                            ])
                            ->default('Active')
                            ->required()
                            ->native(false),

                        TextInput::make('contact_no')
                            ->label('Contact No')
                            ->tel()
                            ->maxLength(50),

                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),

                        TextInput::make('website')
                            ->url()
                            ->maxLength(255),

                        Textarea::make('address')
                            ->rows(3)
                            ->columnSpanFull(),

                        Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}