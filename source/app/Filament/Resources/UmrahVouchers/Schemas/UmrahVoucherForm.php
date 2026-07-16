<?php

namespace App\Filament\Resources\UmrahVouchers\Schemas;

use App\Models\Customer;
use App\Models\UmrahPackage;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class UmrahVoucherForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Voucher Information')
                    ->schema([
                        TextInput::make('voucher_no')
                            ->label('Voucher No')
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
                            ->live()
                            ->required()
                            ->afterStateUpdated(function (?int $state, Set $set): void {
                                $customer = Customer::find($state);

                                $set('lead_guest', $customer?->name);
                                $set('phone', $customer?->mobile);
                            }),

                        Select::make('umrah_package_id')
                            ->label('Umrah Package')
                            ->options(fn (): array => UmrahPackage::query()
                                ->with('umrahGroupFlight')
                                ->where('status', 'Available')
                                ->orderByDesc('id')
                                ->get()
                                ->mapWithKeys(fn (UmrahPackage $package): array => [
                                    $package->id => $package->package_no . ' - ' . $package->title . ' - ' . ($package->flight_route ?: '-'),
                                ])
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'Issued' => 'Issued',
                                'Draft' => 'Draft',
                                'Cancelled' => 'Cancelled',
                            ])
                            ->default('Issued')
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Lead Guest / Travelers')
                    ->schema([
                        TextInput::make('lead_guest')
                            ->label('Lead Guest')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label('Phone')
                            ->maxLength(255),

                        TextInput::make('adults')
                            ->label('Adults')
                            ->numeric()
                            ->default(1)
                            ->required(),

                        TextInput::make('children')
                            ->label('Children')
                            ->numeric()
                            ->default(0)
                            ->required(),

                        TextInput::make('infants')
                            ->label('Infants')
                            ->numeric()
                            ->default(0)
                            ->required(),

                        TextInput::make('room_count')
                            ->label('Rooms')
                            ->numeric()
                            ->default(1),

                        TextInput::make('room_type')
                            ->label('Room Type')
                            ->placeholder('1 x Quad Room / 1 x Triple Room')
                            ->maxLength(255),

                        TextInput::make('pnr')
                            ->label('PNR')
                            ->maxLength(255),

                        TextInput::make('booking_reference')
                            ->label('Booking Reference')
                            ->maxLength(255),
                    ])
                    ->columns(3),

                Section::make('Passenger Details')
                    ->schema([
                        Repeater::make('passengers')
                            ->label(false)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Name')
                                    ->required()
                                    ->maxLength(255),

                                Select::make('type')
                                    ->label('Type')
                                    ->options([
                                        'ADT' => 'Adult',
                                        'CHD' => 'Child',
                                        'INF' => 'Infant',
                                    ])
                                    ->default('ADT')
                                    ->required(),

                                Select::make('gender')
                                    ->label('Gender')
                                    ->options([
                                        'M' => 'Male',
                                        'F' => 'Female',
                                    ])
                                    ->required(),

                                TextInput::make('nationality')
                                    ->label('Nationality')
                                    ->default('PK')
                                    ->maxLength(255),

                                TextInput::make('passport_no')
                                    ->label('Passport No')
                                    ->maxLength(255),

                                DatePicker::make('date_of_birth')
                                    ->label('Date of Birth')
                                    ->native(false),

                                TextInput::make('ticket_no')
                                    ->label('Ticket No')
                                    ->maxLength(255),
                            ])
                            ->columns(7)
                            ->reorderable()
                            ->defaultItems(1)
                            ->columnSpanFull(),
                    ]),

                Section::make('Remarks / Instructions')
                    ->schema([
                        Textarea::make('remarks')
                            ->label('Remarks')
                            ->rows(4),

                        Textarea::make('special_instructions')
                            ->label('Special Instructions')
                            ->rows(4),

                        Textarea::make('terms_conditions')
                            ->label('Terms & Conditions')
                            ->rows(6)
                            ->default("Arrive at the airport 4 hours before departure.\nHotel check-in/check-out depends on hotel policy.\nRoom type and setup are controlled by hotel and may vary.\nKeep passports, tickets and luggage tags secure during the trip.\nMissed flights, transfers or hotel nights due to passenger delay are non-compensable."),
                    ])
                    ->columns(2),
            ]);
    }
}