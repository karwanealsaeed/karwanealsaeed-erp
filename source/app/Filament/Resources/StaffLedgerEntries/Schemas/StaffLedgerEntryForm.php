<?php

namespace App\Filament\Resources\StaffLedgerEntries\Schemas;

use App\Models\Staff;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class StaffLedgerEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Staff Ledger Entry')
                    ->description(
                        'Debit: staff se recoverable amount. Credit: salary, payment, bonus ya reimbursement.'
                    )
                    ->columns(3)
                    ->schema([
                        TextInput::make('entry_no')
                            ->label('Entry No')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto Generated'),

                        Select::make('staff_id')
                            ->label('Staff Member')
                            ->options(fn (): array => Staff::query()
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),

                        DatePicker::make('entry_date')
                            ->label('Entry Date')
                            ->default(today())
                            ->required(),

                        Select::make('entry_side')
                            ->label('Entry Type')
                            ->options([
                                'Debit' => 'Debit — Staff Payable to Company',
                                'Credit' => 'Credit — Company Payable to Staff',
                            ])
                            ->live()
                            ->dehydrated(false)
                            ->required()
                            ->native(false)
                            ->afterStateUpdated(function (
                                ?string $state,
                                Set $set
                            ): void {
                                if ($state === 'Debit') {
                                    $set('credit', 0);
                                }

                                if ($state === 'Credit') {
                                    $set('debit', 0);
                                }
                            })
                            ->afterStateHydrated(function (
                                Select $component,
                                $record
                            ): void {
                                if (! $record) {
                                    return;
                                }

                                $component->state(
                                    (float) $record->debit > 0
                                        ? 'Debit'
                                        : 'Credit'
                                );
                            }),

                        Select::make('category')
                            ->options([
                                'Salary' => 'Salary',
                                'Advance' => 'Advance',
                                'Loan' => 'Loan',
                                'Bonus' => 'Bonus',
                                'Commission' => 'Commission',
                                'Reimbursement' => 'Reimbursement',
                                'Deduction' => 'Deduction',
                                'Recovery' => 'Recovery',
                                'Payment' => 'Payment',
                                'Opening Balance' => 'Opening Balance',
                                'Adjustment' => 'Adjustment',
                                'Other' => 'Other',
                            ])
                            ->searchable()
                            ->required()
                            ->native(false),

                        Select::make('status')
                            ->options([
                                'Pending' => 'Pending',
                                'Approved' => 'Approved',
                                'Rejected' => 'Rejected',
                                'Cancelled' => 'Cancelled',
                            ])
                            ->default('Pending')
                            ->required()
                            ->native(false),

                        TextInput::make('debit')
                            ->label('Debit Amount')
                            ->numeric()
                            ->prefix('PKR')
                            ->default(0)
                            ->minValue(0)
                            ->visible(
                                fn (Get $get): bool =>
                                    $get('entry_side') === 'Debit'
                            )
                            ->required(
                                fn (Get $get): bool =>
                                    $get('entry_side') === 'Debit'
                            ),

                        TextInput::make('credit')
                            ->label('Credit Amount')
                            ->numeric()
                            ->prefix('PKR')
                            ->default(0)
                            ->minValue(0)
                            ->visible(
                                fn (Get $get): bool =>
                                    $get('entry_side') === 'Credit'
                            )
                            ->required(
                                fn (Get $get): bool =>
                                    $get('entry_side') === 'Credit'
                            ),

                        TextInput::make('reference_no')
                            ->label('Reference No')
                            ->maxLength(100),

                        TextInput::make('reference_type')
                            ->label('Reference Type')
                            ->placeholder('Payroll, Expense, Manual Entry')
                            ->maxLength(100),

                        TextInput::make('reference_id')
                            ->label('Reference ID')
                            ->numeric()
                            ->minValue(1),

                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),

                        Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Approval Information')
                    ->columns(3)
                    ->schema([
                        TextInput::make('approver.name')
                            ->label('Approved By')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('approved_at')
                            ->label('Approved At')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('rejecter.name')
                            ->label('Rejected By')
                            ->disabled()
                            ->dehydrated(false),

                        Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}