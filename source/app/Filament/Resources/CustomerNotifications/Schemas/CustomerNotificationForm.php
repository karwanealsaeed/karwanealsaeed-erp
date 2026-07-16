<?php

namespace App\Filament\Resources\CustomerNotifications\Schemas;

use App\Models\Customer;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerNotificationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Notification / Offer')
                    ->description('Send general or customer-specific notifications.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('title')->required()->maxLength(255)->columnSpan(2),

                        Select::make('status')
                            ->options([
                                'Active' => 'Active',
                                'Inactive' => 'Inactive',
                            ])
                            ->default('Active')
                            ->required()
                            ->native(false),

                        Select::make('customer_id')
                            ->label('Specific Customer')
                            ->helperText('Blank chhorne se notification sab customers ko show hogi.')
                            ->options(fn () => Customer::query()->orderBy('name')->pluck('name', 'id')->toArray())
                            ->searchable()
                            ->native(false),

                        Select::make('type')
                            ->options([
                                'Notification' => 'Notification',
                                'Offer' => 'Offer',
                                'Promotion' => 'Promotion',
                                'Alert' => 'Alert',
                            ])
                            ->default('Notification')
                            ->required()
                            ->native(false),

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

                        DatePicker::make('visible_from')->label('Visible From'),
                        DatePicker::make('visible_until')->label('Visible Until'),

                        TextInput::make('action_label')
                            ->label('Button Label')
                            ->placeholder('View Offer / Contact Us'),

                        TextInput::make('action_url')
                            ->label('Button URL')
                            ->url()
                            ->placeholder('https://...'),

                        Textarea::make('message')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}