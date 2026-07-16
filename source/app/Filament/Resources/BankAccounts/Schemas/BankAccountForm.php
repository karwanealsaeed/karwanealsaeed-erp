<?php

namespace App\Filament\Resources\BankAccounts\Schemas;

use App\Models\AccountHead;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BankAccountForm
{
    public static function configure(
        Schema $schema
    ): Schema {
        return $schema
            ->components([
                Section::make('Bank Account Information')
                    ->columns(3)
                    ->schema([
                        Select::make('account_head_id')
                            ->label('GL Account Head')
                            ->options(
                                fn (): array =>
                                    AccountHead::query()
                                        ->where(
                                            'status',
                                            'Active'
                                        )
                                        ->orderBy('code')
                                        ->get()
                                        ->mapWithKeys(
                                            fn (
                                                AccountHead $head
                                            ): array => [
                                                $head->id =>
                                                    $head->code
                                                    . ' - '
                                                    . $head->name
                                                    . ' ('
                                                    . $head->type
                                                    . ')',
                                            ]
                                        )
                                        ->toArray()
                            )
                            ->searchable()
                            ->preload()
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->native(false)
                            ->helperText(
                                'Is bank ka corresponding Bank GL Account Head select karein.'
                            ),

                        TextInput::make('name')
                            ->label('Internal Account Name')
                            ->placeholder(
                                'Meezan Current Account'
                            )
                            ->required()
                            ->maxLength(255),

                        TextInput::make('bank_name')
                            ->label('Bank Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('account_title')
                            ->label('Account Title')
                            ->maxLength(255),

                        TextInput::make('account_number')
                            ->label('Account Number')
                            ->maxLength(100),

                        TextInput::make('iban')
                            ->label('IBAN')
                            ->maxLength(100),

                        TextInput::make('branch')
                            ->label('Branch')
                            ->maxLength(255),

                        TextInput::make('currency')
                            ->default('PKR')
                            ->required()
                            ->maxLength(10),

                        Select::make('status')
                            ->options([
                                'Active' => 'Active',
                                'Inactive' => 'Inactive',
                            ])
                            ->default('Active')
                            ->required()
                            ->native(false),

                        DatePicker::make('opening_date')
                            ->label(
                                'Statement Opening Date'
                            ),

                        TextInput::make('opening_balance')
                            ->label(
                                'Statement Opening Balance'
                            )
                            ->numeric()
                            ->prefix('PKR')
                            ->default(0)
                            ->required(),

                        Textarea::make('notes')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}