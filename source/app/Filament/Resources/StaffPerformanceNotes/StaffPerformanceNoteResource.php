<?php

namespace App\Filament\Resources\StaffPerformanceNotes;

use App\Filament\Resources\StaffPerformanceNotes\Pages\CreateStaffPerformanceNote;
use App\Filament\Resources\StaffPerformanceNotes\Pages\EditStaffPerformanceNote;
use App\Filament\Resources\StaffPerformanceNotes\Pages\ListStaffPerformanceNotes;
use App\Filament\Resources\StaffPerformanceNotes\Pages\ViewStaffPerformanceNote;
use App\Filament\Resources\StaffPerformanceNotes\Schemas\StaffPerformanceNoteForm;
use App\Filament\Resources\StaffPerformanceNotes\Schemas\StaffPerformanceNoteInfolist;
use App\Filament\Resources\StaffPerformanceNotes\Tables\StaffPerformanceNotesTable;
use App\Models\StaffPerformanceNote;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class StaffPerformanceNoteResource extends Resource
{

    use \App\Filament\Concerns\HasRbacResourceAuthorization;
protected static ?string $model = StaffPerformanceNote::class;

    protected static string|BackedEnum|null $navigationIcon =
        'heroicon-o-chart-bar-square';

    protected static string|UnitEnum|null $navigationGroup = 'HR';

    protected static ?string $navigationLabel = 'Performance Notes';

    protected static ?string $modelLabel = 'Performance Note';

    protected static ?string $pluralModelLabel = 'Performance Notes';

    protected static ?string $recordTitleAttribute = 'note_no';

    protected static ?int $navigationSort = 13;

    public static function form(Schema $schema): Schema
    {
        return StaffPerformanceNoteForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return StaffPerformanceNoteInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StaffPerformanceNotesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStaffPerformanceNotes::route('/'),
            'create' => CreateStaffPerformanceNote::route('/create'),
            'view' => ViewStaffPerformanceNote::route('/{record}'),
            'edit' => EditStaffPerformanceNote::route('/{record}/edit'),
        ];
    }
}