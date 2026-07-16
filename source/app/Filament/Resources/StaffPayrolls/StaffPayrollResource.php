<?php

namespace App\Filament\Resources\StaffPayrolls;

use App\Filament\Resources\StaffPayrolls\Pages\CreateStaffPayroll;
use App\Filament\Resources\StaffPayrolls\Pages\EditStaffPayroll;
use App\Filament\Resources\StaffPayrolls\Pages\ListStaffPayrolls;
use App\Filament\Resources\StaffPayrolls\Pages\ViewStaffPayroll;
use App\Filament\Resources\StaffPayrolls\Schemas\StaffPayrollForm;
use App\Filament\Resources\StaffPayrolls\Schemas\StaffPayrollInfolist;
use App\Filament\Resources\StaffPayrolls\Tables\StaffPayrollsTable;
use App\Models\StaffPayroll;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class StaffPayrollResource extends Resource
{
    protected static ?string $model = StaffPayroll::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';

    protected static string | UnitEnum | null $navigationGroup = 'HR';

    protected static ?string $navigationLabel = 'Staff Payroll';

    protected static ?string $modelLabel = 'Staff Payroll';

    protected static ?string $pluralModelLabel = 'Staff Payroll';

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        return (auth()->user()?->isAdmin() ?? false)
            || (auth()->user()?->hasPermission('users_view') ?? false);
    }

    public static function form(Schema $schema): Schema
    {
        return StaffPayrollForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return StaffPayrollInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StaffPayrollsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStaffPayrolls::route('/'),
            'create' => CreateStaffPayroll::route('/create'),
            'view' => ViewStaffPayroll::route('/{record}'),
            'edit' => EditStaffPayroll::route('/{record}/edit'),
        ];
    }
}