<?php

namespace App\Filament\Resources\StaffMembers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StaffMemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Personal Information')
                    ->description('Employee identity and contact details.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('employee_no')
                            ->label('Employee No')
                            ->placeholder('Auto generated')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        TextInput::make('father_name')
                            ->label('Father Name')
                            ->maxLength(255),

                        TextInput::make('cnic')
                            ->label('CNIC / National ID')
                            ->maxLength(255),

                        TextInput::make('mobile')
                            ->label('Mobile')
                            ->maxLength(255),

                        TextInput::make('whatsapp')
                            ->label('WhatsApp')
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                    ]),

                Section::make('Employment Details')
                    ->description('Designation, department and employment dates.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('designation')
                            ->label('Designation')
                            ->maxLength(255),

                        Select::make('department')
                            ->label('Department')
                            ->options([
                                'Admin' => 'Admin',
                                'Sales' => 'Sales',
                                'Accounts' => 'Accounts',
                                'Operations' => 'Operations',
                                'Visa' => 'Visa',
                                'Ticketing' => 'Ticketing',
                                'Umrah' => 'Umrah',
                                'Marketing' => 'Marketing',
                            ])
                            ->searchable()
                            ->native(false),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'Active' => 'Active',
                                'Inactive' => 'Inactive',
                                'Left' => 'Left',
                            ])
                            ->default('Active')
                            ->required()
                            ->native(false),

                        DatePicker::make('joining_date')
                            ->label('Joining Date'),

                        DatePicker::make('leaving_date')
                            ->label('Leaving Date'),

                        TextInput::make('basic_salary')
                            ->label('Basic Salary')
                            ->numeric()
                            ->default(0)
                            ->prefix('PKR'),
                    ]),

                Section::make('Payment & Bank Details')
                    ->description('Preferred salary payment method.')
                    ->columns(3)
                    ->schema([
                        Select::make('payment_method')
                            ->label('Payment Method')
                            ->options([
                                'Cash' => 'Cash',
                                'Bank Transfer' => 'Bank Transfer',
                                'JazzCash' => 'JazzCash',
                                'EasyPaisa' => 'EasyPaisa',
                                'Cheque' => 'Cheque',
                            ])
                            ->native(false),

                        TextInput::make('bank_name')
                            ->label('Bank Name')
                            ->maxLength(255),

                        TextInput::make('bank_account_no')
                            ->label('Bank Account / IBAN')
                            ->maxLength(255),
                    ]),

                Section::make('Address & Notes')
                    ->columns(1)
                    ->schema([
                        Textarea::make('address')
                            ->label('Address')
                            ->rows(3),

                        Textarea::make('notes')
                            ->label('Internal Notes')
                            ->rows(3),
                    ]),
            ]);
    }
}