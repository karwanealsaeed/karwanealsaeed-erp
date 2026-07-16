<?php

namespace App\Filament\Resources\ExpenseCategories;

use App\Filament\Resources\ExpenseCategories\Pages\CreateExpenseCategory;
use App\Filament\Resources\ExpenseCategories\Pages\EditExpenseCategory;
use App\Filament\Resources\ExpenseCategories\Pages\ListExpenseCategories;
use App\Filament\Resources\ExpenseCategories\Pages\ViewExpenseCategory;
use App\Filament\Resources\ExpenseCategories\Schemas\ExpenseCategoryForm;
use App\Filament\Resources\ExpenseCategories\Schemas\ExpenseCategoryInfolist;
use App\Filament\Resources\ExpenseCategories\Tables\ExpenseCategoriesTable;
use App\Models\ExpenseCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ExpenseCategoryResource extends Resource
{

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-tag';


    protected static ?string $model = ExpenseCategory::class;



    protected static string | \UnitEnum | null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 7;

    protected static ?string $recordTitleAttribute = 'name';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasPermission('expense_categories.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission('expense_categories.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasPermission('expense_categories.edit') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasPermission('expense_categories.delete') ?? false;
    }
public static function form(Schema $schema): Schema
    {
        return ExpenseCategoryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ExpenseCategoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExpenseCategoriesTable::configure($table);
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
            'index' => ListExpenseCategories::route('/'),
            'create' => CreateExpenseCategory::route('/create'),
            'view' => ViewExpenseCategory::route('/{record}'),
            'edit' => EditExpenseCategory::route('/{record}/edit'),
        ];
    }
}










