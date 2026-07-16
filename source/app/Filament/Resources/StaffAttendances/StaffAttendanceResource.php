<?php

namespace App\Filament\Resources\StaffAttendances;

use App\Filament\Resources\StaffAttendances\Pages\CreateStaffAttendance;
use App\Filament\Resources\StaffAttendances\Pages\EditStaffAttendance;
use App\Filament\Resources\StaffAttendances\Pages\ListStaffAttendances;
use App\Filament\Resources\StaffAttendances\Pages\ViewStaffAttendance;
use App\Filament\Resources\StaffAttendances\Schemas\StaffAttendanceForm;
use App\Filament\Resources\StaffAttendances\Schemas\StaffAttendanceInfolist;
use App\Filament\Resources\StaffAttendances\Tables\StaffAttendancesTable;
use App\Models\StaffAttendance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class StaffAttendanceResource extends Resource
{
    protected static ?string $model = StaffAttendance::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-clock';

    protected static string | UnitEnum | null $navigationGroup = 'HR';

    protected static ?string $navigationLabel = 'Staff Attendance';

    protected static ?string $modelLabel = 'Staff Attendance';

    protected static ?string $pluralModelLabel = 'Staff Attendance';

    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return (auth()->user()?->isAdmin() ?? false)
            || (auth()->user()?->hasPermission('users_view') ?? false);
    }

    public static function form(Schema $schema): Schema
    {
        return StaffAttendanceForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return StaffAttendanceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StaffAttendancesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStaffAttendances::route('/'),
            'create' => CreateStaffAttendance::route('/create'),
            'view' => ViewStaffAttendance::route('/{record}'),
            'edit' => EditStaffAttendance::route('/{record}/edit'),
        ];
    }
}