<?php

namespace App\Filament\Resources\CustomerFamilyMembers\Schemas;

use App\Models\Customer;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerFamilyMemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Family Member')
                    ->columns(3)
                    ->schema([
                        Select::make('customer_id')
                            ->label('Customer')
                            ->options(fn () => Customer::query()->orderBy('name')->pluck('name', 'id')->toArray())
                            ->searchable()
                            ->required()
                            ->native(false),

                        TextInput::make('name')->required()->maxLength(255)->columnSpan(2),

                        Select::make('relationship')
                            ->options([
                                'Father' => 'Father',
                                'Mother' => 'Mother',
                                'Wife' => 'Wife',
                                'Husband' => 'Husband',
                                'Son' => 'Son',
                                'Daughter' => 'Daughter',
                                'Brother' => 'Brother',
                                'Sister' => 'Sister',
                                'Other' => 'Other',
                            ])
                            ->searchable()
                            ->native(false),

                        TextInput::make('cnic')->label('CNIC')->maxLength(255),
                        TextInput::make('passport_no')->label('Passport No')->maxLength(255),
                        DatePicker::make('date_of_birth')->label('Date of Birth'),
                        TextInput::make('mobile')->maxLength(255),

                        Select::make('status')
                            ->options([
                                'Active' => 'Active',
                                'Inactive' => 'Inactive',
                            ])
                            ->default('Active')
                            ->required()
                            ->native(false),

                        Textarea::make('notes')->rows(4)->columnSpanFull(),
                    ]),
            ]);
    }
}