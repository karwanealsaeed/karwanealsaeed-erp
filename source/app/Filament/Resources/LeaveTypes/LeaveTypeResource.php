<?php

namespace App\Filament\Resources\LeaveTypes;

use App\Filament\Resources\LeaveTypes\Pages\CreateLeaveType;
use App\Filament\Resources\LeaveTypes\Pages\EditLeaveType;
use App\Filament\Resources\LeaveTypes\Pages\ListLeaveTypes;
use App\Filament\Resources\LeaveTypes\Pages\ViewLeaveType;
use App\Filament\Resources\LeaveTypes\Schemas\LeaveTypeForm;
use App\Filament\Resources\LeaveTypes\Schemas\LeaveTypeInfolist;
use App\Filament\Resources\LeaveTypes\Tables\LeaveTypesTable;
use App\Models\LeaveType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class LeaveTypeResource extends Resource
{
    protected static ?string $model = LeaveType::class;

    protected static string|BackedEnum|null $navigationIcon =
        'heroicon-o-rectangle-stack';

    protected static string|UnitEnum|null $navigationGroup = 'HR';

    protected static ?string $navigationLabel = 'Leave Types';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return LeaveTypeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LeaveTypeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LeaveTypesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLeaveTypes::route('/'),
            'create' => CreateLeaveType::route('/create'),
            'view' => ViewLeaveType::route('/{record}'),
            'edit' => EditLeaveType::route('/{record}/edit'),
        ];
    }
}