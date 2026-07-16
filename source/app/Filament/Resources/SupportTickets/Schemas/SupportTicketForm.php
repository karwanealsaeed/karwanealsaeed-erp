<?php

namespace App\Filament\Resources\SupportTickets\Schemas;

use App\Models\Customer;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SupportTicketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ticket Details')
                    ->columns(3)
                    ->schema([
                        TextInput::make('ticket_no')->label('Ticket No')->disabled()->dehydrated(false),

                        Select::make('customer_id')
                            ->label('Customer')
                            ->options(fn () => Customer::query()->orderBy('name')->pluck('name', 'id')->toArray())
                            ->searchable()
                            ->native(false),

                        Select::make('status')
                            ->options([
                                'Open' => 'Open',
                                'In Progress' => 'In Progress',
                                'Waiting Customer' => 'Waiting Customer',
                                'Closed' => 'Closed',
                            ])
                            ->default('Open')
                            ->required()
                            ->native(false),

                        TextInput::make('name')->required()->maxLength(255),
                        TextInput::make('mobile')->required()->maxLength(255),
                        TextInput::make('email')->email()->maxLength(255),

                        Select::make('service_type')
                            ->label('Service Type')
                            ->options([
                                'Umrah Package' => 'Umrah Package',
                                'Visit Visa' => 'Visit Visa',
                                'Air Ticketing' => 'Air Ticketing',
                                'Hotel Booking' => 'Hotel Booking',
                                'Travel Insurance' => 'Travel Insurance',
                                'Pakistan Tours' => 'Pakistan Tours',
                            ])
                            ->searchable()
                            ->native(false),

                        Select::make('priority')
                            ->options([
                                'Low' => 'Low',
                                'Normal' => 'Normal',
                                'High' => 'High',
                                'Urgent' => 'Urgent',
                            ])
                            ->default('Normal')
                            ->native(false),

                        DateTimePicker::make('closed_at')->label('Closed At'),

                        TextInput::make('subject')->required()->maxLength(255)->columnSpanFull(),
                        Textarea::make('message')->rows(5)->required()->columnSpanFull(),
                    ]),
            ]);
    }
}