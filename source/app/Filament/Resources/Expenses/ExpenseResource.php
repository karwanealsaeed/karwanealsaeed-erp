<?php

namespace App\Filament\Resources\Expenses;

use App\Filament\Resources\Expenses\Pages\CreateExpense;
use App\Filament\Resources\Expenses\Pages\EditExpense;
use App\Filament\Resources\Expenses\Pages\ListExpenses;
use App\Filament\Resources\Expenses\Pages\ViewExpense;
use App\Filament\Resources\Expenses\Schemas\ExpenseForm;
use App\Filament\Resources\Expenses\Schemas\ExpenseInfolist;
use App\Filament\Resources\Expenses\Tables\ExpensesTable;
use App\Models\Expense;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ExpenseResource extends Resource
{

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-wallet';


    protected static ?string $model = Expense::class;



    protected static string | \UnitEnum | null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 6;

    protected static ?string $recordTitleAttribute = 'expense_date';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasPermission('expenses.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission('expenses.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasPermission('expenses.edit') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasPermission('expenses.delete') ?? false;
    }
public static function form(Schema $schema): Schema
    {
        return ExpenseForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ExpenseInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExpensesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExpenses::route('/'),
            'create' => CreateExpense::route('/create'),
            'view' => ViewExpense::route('/{record}'),
            'edit' => EditExpense::route('/{record}/edit'),
        ];
    }
}










