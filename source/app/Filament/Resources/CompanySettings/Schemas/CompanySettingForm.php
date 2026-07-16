<?php

namespace App\Filament\Resources\CompanySettings\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CompanySettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('company_name')
                    ->label('Company Name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                TextInput::make('short_name')
                    ->label('Short Name')
                    ->maxLength(255),

                FileUpload::make('logo')
                    ->label('Company Logo')
                    ->image()
                    ->disk('public')
                    ->directory('company-logos')
                    ->visibility('public')
                    ->columnSpanFull(),

                TextInput::make('phone')
                    ->label('Phone')
                    ->tel()
                    ->maxLength(255),

                TextInput::make('mobile')
                    ->label('Mobile')
                    ->tel()
                    ->maxLength(255),

                TextInput::make('whatsapp')
                    ->label('WhatsApp')
                    ->tel()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255),

                TextInput::make('website')
                    ->label('Website')
                    ->maxLength(255),

                Textarea::make('address')
                    ->label('Address')
                    ->rows(3)
                    ->columnSpanFull(),

                TextInput::make('city')
                    ->label('City')
                    ->maxLength(255),

                TextInput::make('country')
                    ->label('Country')
                    ->maxLength(255),

                TextInput::make('ntn_no')
                    ->label('NTN No')
                    ->maxLength(255),

                TextInput::make('license_no')
                    ->label('License No')
                    ->maxLength(255),

                TextInput::make('bank_name')
                    ->label('Bank Name')
                    ->maxLength(255),

                TextInput::make('bank_account_title')
                    ->label('Bank Account Title')
                    ->maxLength(255),

                TextInput::make('bank_account_no')
                    ->label('Bank Account No')
                    ->maxLength(255),

                TextInput::make('bank_iban')
                    ->label('Bank IBAN')
                    ->maxLength(255),

                Textarea::make('notes')
                    ->label('Notes')
                    ->rows(3)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}
