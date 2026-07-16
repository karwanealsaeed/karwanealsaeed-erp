<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('company_name'),
                TextInput::make('mobile')
                    ->required(),
                TextInput::make('whatsapp'),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                Textarea::make('address')
                    ->columnSpanFull(),
                TextInput::make('city'),
                TextInput::make('country')
                    ->required()
                    ->default('Pakistan'),
                Select::make('supplier_type')
                    ->options([
            'Airline' => 'Airline',
            'Hotel' => 'Hotel',
            'Visa' => 'Visa',
            'Transport' => 'Transport',
            'Other' => 'Other',
        ])
                    ->default('Other')
                    ->required(),
                Select::make('status')
                    ->options(['Active' => 'Active', 'Inactive' => 'Inactive'])
                    ->default('Active')
                    ->required(),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
