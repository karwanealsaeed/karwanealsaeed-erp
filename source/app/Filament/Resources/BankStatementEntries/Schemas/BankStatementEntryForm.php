<?php

namespace App\Filament\Resources\BankStatementEntries\Schemas;

use App\Models\BankAccount;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class BankStatementEntryForm
{
    public static function configure(
        Schema $schema
    ): Schema {
        return $schema
            ->components([
                Section::make('Bank Statement Entry')
                    ->description(
                        'Deposit money received hai; Withdrawal money paid hai.'
                    )
                    ->columns(3)
                    ->schema([
                        TextInput::make('entry_no')
                            ->label('Entry No')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto Generated'),

                        Select::make('bank_account_id')
                            ->label('Bank Account')
                            ->options(
                                fn (): array =>
                                    BankAccount::query()
                                        ->where(
                                            'status',
                                            'Active'
                                        )
                                        ->orderBy('bank_name')
                                        ->get()
                                        ->mapWithKeys(
                                            fn (
                                                BankAccount $account
                                            ): array => [
                                                $account->id =>
                                                    $account
                                                        ->display_name,
                                            ]
                                        )
                                        ->toArray()
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),

                        DatePicker::make('statement_date')
                            ->label('Statement Date')
                            ->default(today())
                            ->required(),

                        Select::make('transaction_type')
                            ->label('Transaction Type')
                            ->options([
                                'Deposit' => 'Deposit / Money Received',
                                'Withdrawal' => 'Withdrawal / Money Paid',
                            ])
                            ->live()
                            ->dehydrated(false)
                            ->required()
                            ->native(false)
                            ->afterStateUpdated(
                                function (
                                    ?string $state,
                                    Set $set
                                ): void {
                                    if ($state === 'Deposit') {
                                        $set('withdrawal', 0);
                                    }

                                    if (
                                        $state
                                        === 'Withdrawal'
                                    ) {
                                        $set('deposit', 0);
                                    }
                                }
                            )
                            ->afterStateHydrated(
                                function (
                                    Select $component,
                                    $record
                                ): void {
                                    if (! $record) {
                                        return;
                                    }

                                    $component->state(
                                        (float)
                                            $record->deposit
                                            > 0
                                            ? 'Deposit'
                                            : 'Withdrawal'
                                    );
                                }
                            ),

                        TextInput::make('deposit')
                            ->label('Deposit Amount')
                            ->numeric()
                            ->prefix('PKR')
                            ->default(0)
                            ->minValue(0)
                            ->visible(
                                fn (Get $get): bool =>
                                    $get('transaction_type')
                                    === 'Deposit'
                            )
                            ->required(
                                fn (Get $get): bool =>
                                    $get('transaction_type')
                                    === 'Deposit'
                            ),

                        TextInput::make('withdrawal')
                            ->label('Withdrawal Amount')
                            ->numeric()
                            ->prefix('PKR')
                            ->default(0)
                            ->minValue(0)
                            ->visible(
                                fn (Get $get): bool =>
                                    $get('transaction_type')
                                    === 'Withdrawal'
                            )
                            ->required(
                                fn (Get $get): bool =>
                                    $get('transaction_type')
                                    === 'Withdrawal'
                            ),

                        TextInput::make('reference_no')
                            ->label('Bank Reference')
                            ->maxLength(150),

                        TextInput::make('cheque_no')
                            ->label('Cheque No')
                            ->maxLength(100),

                        TextInput::make('running_balance')
                            ->label('Statement Balance')
                            ->numeric()
                            ->prefix('PKR'),

                        Select::make(
                            'reconciliation_status'
                        )
                            ->label(
                                'Reconciliation Status'
                            )
                            ->options([
                                'Unmatched' => 'Unmatched',
                                'Matched' => 'Matched',
                                'Ignored' => 'Ignored',
                            ])
                            ->default('Unmatched')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->native(false),

                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),

                        Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}