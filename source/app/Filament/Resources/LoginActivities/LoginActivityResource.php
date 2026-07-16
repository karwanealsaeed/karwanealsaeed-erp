<?php

namespace App\Filament\Resources\LoginActivities;

use App\Filament\Resources\LoginActivities\Pages\CreateLoginActivity;
use App\Filament\Resources\LoginActivities\Pages\EditLoginActivity;
use App\Filament\Resources\LoginActivities\Pages\ListLoginActivities;
use App\Filament\Resources\LoginActivities\Pages\ViewLoginActivity;
use App\Filament\Resources\LoginActivities\Schemas\LoginActivityForm;
use App\Filament\Resources\LoginActivities\Schemas\LoginActivityInfolist;
use App\Filament\Resources\LoginActivities\Tables\LoginActivitiesTable;
use App\Models\LoginActivity;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class LoginActivityResource extends Resource
{
    protected static ?string $model = LoginActivity::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-shield-check';

    protected static string | UnitEnum | null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Login History';

    protected static ?string $modelLabel = 'Login Activity';

    protected static ?string $pluralModelLabel = 'Login History';

    protected static ?int $navigationSort = 20;

    public static function canViewAny(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return LoginActivityForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LoginActivityInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LoginActivitiesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLoginActivities::route('/'),
            'create' => CreateLoginActivity::route('/create'),
            'view' => ViewLoginActivity::route('/{record}'),
            'edit' => EditLoginActivity::route('/{record}/edit'),
        ];
    }
}