<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentExpenses extends TableWidget
{
    protected static ?string $heading = 'Recent Expenses';

    protected int|string|array $columnSpan = 'full';


    public static function canView(): bool
    {
        return auth()->user()?->hasPermission('expenses.view') ?? false;
    }
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Expense::query()->latest()->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('expense_no')
                    ->label('Expense No')
                    ->searchable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable(),

                Tables\Columns\TextColumn::make('expense_date')
                    ->label('Date')
                    ->date(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('PKR', divideBy: 1),

                Tables\Columns\BadgeColumn::make('payment_method')
                    ->label('Method'),

                Tables\Columns\TextColumn::make('reference_no')
                    ->label('Reference')
                    ->placeholder('-'),
            ])
            ->paginated(false);
    }
}
