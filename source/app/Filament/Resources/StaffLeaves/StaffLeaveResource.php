<?php

namespace App\Filament\Resources\StaffLeaves;

use App\Filament\Resources\StaffLeaves\Pages\CreateStaffLeave;
use App\Filament\Resources\StaffLeaves\Pages\EditStaffLeave;
use App\Filament\Resources\StaffLeaves\Pages\ListStaffLeaves;
use App\Filament\Resources\StaffLeaves\Pages\ViewStaffLeave;
use App\Filament\Resources\StaffLeaves\Schemas\StaffLeaveForm;
use App\Filament\Resources\StaffLeaves\Schemas\StaffLeaveInfolist;
use App\Filament\Resources\StaffLeaves\Tables\StaffLeavesTable;
use App\Models\StaffLeave;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class StaffLeaveResource extends Resource
{
    protected static ?string $model = StaffLeave::class;

    protected static string|BackedEnum|null $navigationIcon =
        'heroicon-o-calendar-days';

    protected static string|UnitEnum|null $navigationGroup = 'HR';

    protected static ?string $navigationLabel = 'Leave Management';

    protected static ?string $modelLabel = 'Staff Leave';

    protected static ?string $pluralModelLabel = 'Staff Leaves';

    protected static ?string $recordTitleAttribute = 'leave_no';

    protected static ?int $navigationSort = 11;

    public static function form(Schema $schema): Schema
    {
        return StaffLeaveForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return StaffLeaveInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StaffLeavesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStaffLeaves::route('/'),
            'create' => CreateStaffLeave::route('/create'),
            'view' => ViewStaffLeave::route('/{record}'),
            'edit' => EditStaffLeave::route('/{record}/edit'),
        ];
    }
}