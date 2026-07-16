<?php

namespace App\Filament\Resources\VisaApplications\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class VisaApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('application_no')
                    ->label('Application No')
                    ->disabled()
                    ->dehydrated(),

                Select::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required()
                    ->afterStateUpdated(function ($state, Set $set) {

                        $customer = \App\Models\Customer::find($state);

                        if ($customer) {
                            $set('passport_no', $customer->passport_no);
                        }

                    }),

                Select::make('country_id')
                    ->label('Country')
                    ->relationship('country', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('visa_type_id')
                    ->label('Visa Type')
                    ->relationship('visaType', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('passport_no')
                    ->label('Passport Number')
                    ->required(),

                Select::make('passport_status')
                    ->label('Passport Status')
                    ->options([
                        'Pending' => 'Pending',
                        'Received' => 'Received',
                        'Embassy' => 'Embassy',
                        'Ready' => 'Ready',
                        'Returned' => 'Returned',
                    ])
                    ->default('Pending')
                    ->required(),

                DatePicker::make('passport_received_date')
                    ->label('Passport Received Date'),

                DatePicker::make('passport_return_date')
                    ->label('Passport Return Date'),

                DatePicker::make('travel_date')
                    ->label('Travel Date'),

                DatePicker::make('return_date')
                    ->label('Return Date'),

                TextInput::make('visa_fee')
                    ->label('Visa Fee')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->prefix('PKR')
                    ->live()
                    ->dehydrateStateUsing(fn ($state) => $state ?: 0)
                    ->afterStateUpdated(function (Get $get, Set $set) {

                        $visa = (float) ($get('visa_fee') ?? 0);
                        $service = (float) ($get('service_fee') ?? 0);
                        $paid = (float) ($get('paid_amount') ?? 0);

                        $total = $visa + $service;

                        $set('total_amount', $total);
                        $set('balance_amount', max($total - $paid, 0));
                    }),

                TextInput::make('service_fee')
                    ->label('Service Fee')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->prefix('PKR')
                    ->live()
                    ->dehydrateStateUsing(fn ($state) => $state ?: 0)
                    ->afterStateUpdated(function (Get $get, Set $set) {

                        $visa = (float) ($get('visa_fee') ?? 0);
                        $service = (float) ($get('service_fee') ?? 0);
                        $paid = (float) ($get('paid_amount') ?? 0);

                        $total = $visa + $service;

                        $set('total_amount', $total);
                        $set('balance_amount', max($total - $paid, 0));
                    }),

                TextInput::make('total_amount')
                    ->label('Total Amount')
                    ->numeric()
                    ->prefix('PKR')
                    ->disabled()
                    ->dehydrated(),

                TextInput::make('paid_amount')
                    ->label('Paid Amount')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->prefix('PKR')
                    ->live()
                    ->dehydrateStateUsing(fn ($state) => $state ?: 0)
                    ->afterStateUpdated(function (Get $get, Set $set) {

                        $total = (float) ($get('total_amount') ?? 0);
                        $paid = (float) ($get('paid_amount') ?? 0);

                        $set('balance_amount', max($total - $paid, 0));
                    }),

                TextInput::make('balance_amount')
                    ->label('Balance Amount')
                    ->numeric()
                    ->prefix('PKR')
                    ->disabled()
                    ->dehydrated()
                    ->default(0),

                Select::make('status')
                    ->label('Visa Status')
                    ->options([
                        'Draft' => 'Draft',
                        'Applied' => 'Applied',
                        'Processing' => 'Processing',
                        'Approved' => 'Approved',
                        'Rejected' => 'Rejected',
                        'Delivered' => 'Delivered',
                    ])
                    ->default('Draft')
                    ->required(),

                Textarea::make('notes')
                    ->label('Notes')
                    ->rows(4)
                    ->columnSpanFull(),

            ]);
    }
}
