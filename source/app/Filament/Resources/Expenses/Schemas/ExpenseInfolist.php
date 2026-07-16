<?php

namespace App\Filament\Resources\Expenses\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ExpenseInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextEntry::make('expense_no')
                    ->label('Expense No'),

                TextEntry::make('category.name')
                    ->label('Category'),

                TextEntry::make('expense_date')
                    ->label('Expense Date')
                    ->date('d M Y'),

                TextEntry::make('amount')
                    ->label('Amount')
                    ->money('PKR', divideBy: 1),

                TextEntry::make('payment_method')
                    ->label('Payment Method')
                    ->badge(),

                TextEntry::make('reference_no')
                    ->label('Reference No')
                    ->placeholder('-'),

                TextEntry::make('description')
                    ->label('Description')
                    ->placeholder('-')
                    ->columnSpanFull(),

                TextEntry::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y h:i A'),

                TextEntry::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d M Y h:i A'),

            ]);
    }
}
