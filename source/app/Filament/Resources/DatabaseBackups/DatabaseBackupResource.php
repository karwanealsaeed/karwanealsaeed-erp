<?php

namespace App\Filament\Resources\DatabaseBackups;
use App\Filament\Resources\DatabaseBackups\Pages\ViewDatabaseBackup;
use App\Filament\Resources\DatabaseBackups\Pages\ListDatabaseBackups;
use App\Filament\Resources\DatabaseBackups\Tables\DatabaseBackupsTable;
use App\Models\DatabaseBackup;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class DatabaseBackupResource extends Resource
{

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-circle-stack';


    protected static ?string $model = DatabaseBackup::class;


    protected static string | \UnitEnum | null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Database Backups';

    protected static ?string $modelLabel = 'Database Backup';

    protected static ?string $pluralModelLabel = 'Database Backups';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasPermission('database_backups.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission('database_backups.create') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->hasPermission('database_backups.delete') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return DatabaseBackupsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDatabaseBackups::route('/'),
'view' => ViewDatabaseBackup::route('/{record}'),
        ];
    }
}




