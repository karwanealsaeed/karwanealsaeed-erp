<?php

namespace App\Filament\Resources\SupportInquiries\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SupportInquiryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Inquiry Details')
                    ->columns(3)
                    ->schema([
                        TextInput::make('inquiry_no')
                            ->label('Inquiry No')
                            ->disabled()
                            ->dehydrated(false),

                        Select::make('status')
                            ->options([
                                'New' => 'New',
                                'Contacted' => 'Contacted',
                                'In Progress' => 'In Progress',
                                'Closed' => 'Closed',
                            ])
                            ->default('New')
                            ->required(),

                        DateTimePicker::make('contacted_at')
                            ->label('Contacted At'),

                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('mobile')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),

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
                            ->searchable(),

                        TextInput::make('subject')
                            ->maxLength(255)
                            ->columnSpan(2),

                        Textarea::make('message')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull(),

                        Textarea::make('smart_reply')
                            ->label('Smart Reply')
                            ->rows(3)
                            ->columnSpanFull(),

                        Textarea::make('admin_notes')
                            ->label('Admin Notes')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}