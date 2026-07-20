<?php

namespace App\Filament\Resources\Tasks;

use App\Filament\Resources\Tasks\Pages\CreateTask;
use App\Filament\Resources\Tasks\Pages\EditTask;
use App\Filament\Resources\Tasks\Pages\ListTasks;
use App\Filament\Resources\Tasks\Pages\ViewTask;
use App\Filament\Resources\Tasks\Schemas\TaskForm;
use App\Filament\Resources\Tasks\Schemas\TaskInfolist;
use App\Filament\Resources\Tasks\Tables\TasksTable;
use App\Models\Task;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static string | \BackedEnum | null $navigationIcon =
        'heroicon-o-calendar-days';

    protected static string | \UnitEnum | null $navigationGroup =
        'CRM & Follow-ups';

    protected static ?string $navigationLabel =
        'Tasks & Follow-ups';

    protected static ?string $modelLabel = 'Task';

    protected static ?string $pluralModelLabel =
        'Tasks & Follow-ups';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return TaskForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TaskInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TasksTable::configure($table);
    }

    public static function canViewAny(): bool
    {
        return static::hasPermission('tasks.view');
    }

    public static function canView(Model $record): bool
    {
        return static::hasPermission('tasks.view');
    }

    public static function canCreate(): bool
    {
        return static::hasPermission('tasks.create');
    }

    public static function canEdit(Model $record): bool
    {
        return static::hasPermission('tasks.edit');
    }

    public static function canDelete(Model $record): bool
    {
        return static::hasPermission('tasks.delete');
    }

    public static function canDeleteAny(): bool
    {
        return static::hasPermission('tasks.delete');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTasks::route('/'),
            'create' => CreateTask::route('/create'),
            'view' => ViewTask::route('/{record}'),
            'edit' => EditTask::route('/{record}/edit'),
        ];
    }

    private static function hasPermission(
        string $permission,
    ): bool {
        return (auth()->user()?->isAdmin() ?? false)
            || (
                auth()->user()?->hasPermission(
                    $permission,
                ) ?? false
            );
    }
}