<?php

namespace App\Filament\Resources\Expenses\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ExpenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Select::make('expense_category_id')
                    ->label('Expense Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                DatePicker::make('expense_date')
                    ->required()
                    ->default(now()),

                TextInput::make('amount')
                    ->numeric()
                    ->prefix('PKR')
                    ->required(),

                Select::make('payment_method')
                    ->options([
                        'Cash' => 'Cash',
                        'Bank' => 'Bank',
                        'JazzCash' => 'JazzCash',
                        'EasyPaisa' => 'EasyPaisa',
                        'Cheque' => 'Cheque',
                    ])
                    ->required(),
                   TextInput::make('expense_no')
                    ->label('Expense No')
                    ->disabled()
                    ->dehydrated(),
                TextInput::make('reference_no')
                    ->label('Reference No'),

                Textarea::make('description')
                    ->rows(4)
                    ->columnSpanFull(),

            ]);
    }
}
