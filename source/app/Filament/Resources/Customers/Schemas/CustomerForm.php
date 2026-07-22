<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer / Agent Information')
                    ->description('B2C customers aur B2B agents ka master record.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('customer_code')
                            ->label('Customer Code')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto-generated on save'),
                        TextInput::make('name')
                            ->label('Customer / Agent Name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        Select::make('customer_type')
                            ->label('Customer Type')
                            ->options([
                                'Customer' => 'Customer',
                                'Agent' => 'Agent',
                            ])
                            ->default('Customer')
                            ->required()
                            ->native(false),

                        TextInput::make('father_name')
                            ->label('Father / Husband Name')
                            ->maxLength(255),

                        TextInput::make('cnic')
                            ->label('CNIC')
                            ->maxLength(255),

                        TextInput::make('passport_no')
                            ->label('Passport No')
                            ->maxLength(255),

                        TextInput::make('mobile')
                            ->label('Mobile')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('whatsapp')
                            ->label('WhatsApp')
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),

                        DatePicker::make('date_of_birth')
                            ->label('Date of Birth'),

                        TextInput::make('city')
                            ->label('City')
                            ->maxLength(255),

                        TextInput::make('country')
                            ->label('Country')
                            ->default('Pakistan')
                            ->maxLength(255),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'Active' => 'Active',
                                'Inactive' => 'Inactive',
                                'Blocked' => 'Blocked',
                            ])
                            ->default('Active')
                            ->required()
                            ->native(false),

                        Textarea::make('address')
                            ->label('Address')
                            ->rows(3)
                            ->columnSpanFull(),

                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Agent Portal Access')
                    ->description('Ye login sirf Agent Portal ke liye hai. Normal customer ke liye password blank chhor dein.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('portal_password')
                            ->label('Agent Portal Password')
                            ->password()
                            ->revealable()
                            ->helperText('Agent ko portal login dene ke liye password set karein. Edit par blank chhorne se old password same rahega.')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                            ->autocomplete('new-password'),

                        DateTimePicker::make('portal_registered_at')
                            ->label('Portal Activated At')
                            ->disabled()
                            ->dehydrated(false),

                        DateTimePicker::make('last_portal_login_at')
                            ->label('Last Portal Login')
                            ->disabled()
                            ->dehydrated(false),
                    ]),
            ]);
    }
}