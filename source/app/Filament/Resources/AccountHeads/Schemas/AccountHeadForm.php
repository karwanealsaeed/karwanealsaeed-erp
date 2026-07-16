<?php

namespace App\Filament\Resources\AccountHeads\Schemas;

use App\Models\AccountHead;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AccountHeadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account Details')
                    ->columns(3)
                    ->schema([
                        TextInput::make('code')
                            ->label('Account Code')
                            ->placeholder('Auto if blank')
                            ->maxLength(255),

                        TextInput::make('name')
                            ->label('Account Name')
                            ->required()
                            ->maxLength(255),

                        Select::make('type')
                            ->required()
                            ->options([
                                'Asset' => 'Asset',
                                'Liability' => 'Liability',
                                'Equity' => 'Equity',
                                'Income' => 'Income',
                                'Expense' => 'Expense',
                            ])
                            ->native(false),

                        Select::make('sub_type')
                            ->label('Sub Type')
                            ->options([
                                'Cash' => 'Cash',
                                'Bank' => 'Bank',
                                'Receivable' => 'Receivable',
                                'Payable' => 'Payable',
                                'Capital' => 'Capital',
                                'Sales' => 'Sales',
                                'Office' => 'Office',
                                'Salary' => 'Salary',
                                'Rent' => 'Rent',
                                'Marketing' => 'Marketing',
                                'Misc' => 'Misc',
                            ])
                            ->searchable()
                            ->native(false),

                        Select::make('parent_id')
                            ->label('Parent Account')
                            ->options(fn () => AccountHead::query()->orderBy('code')->pluck('name', 'id'))
                            ->searchable()
                            ->native(false),

                        Select::make('status')
                            ->options([
                                'Active' => 'Active',
                                'Inactive' => 'Inactive',
                            ])
                            ->default('Active')
                            ->required()
                            ->native(false),

                        TextInput::make('opening_debit')
                            ->label('Opening Debit')
                            ->numeric()
                            ->default(0),

                        TextInput::make('opening_credit')
                            ->label('Opening Credit')
                            ->numeric()
                            ->default(0),

                        Textarea::make('notes')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}