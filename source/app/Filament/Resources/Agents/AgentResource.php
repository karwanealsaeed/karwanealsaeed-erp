<?php

namespace App\Filament\Resources\Agents;

use App\Filament\Resources\Agents\Pages\CreateAgent;
use App\Filament\Resources\Agents\Pages\EditAgent;
use App\Filament\Resources\Agents\Pages\ListAgents;
use App\Filament\Resources\Agents\Pages\ViewAgent;
use App\Filament\Resources\Agents\Schemas\AgentForm;
use App\Filament\Resources\Agents\Schemas\AgentInfolist;
use App\Filament\Resources\Agents\Tables\AgentsTable;
use App\Models\Customer;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AgentResource extends Resource
{
    protected static string | \BackedEnum | null $navigationIcon =
        'heroicon-o-briefcase';

    protected static ?string $model = Customer::class;

    protected static string | \UnitEnum | null $navigationGroup =
        'Customers';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Agent';

    protected static ?string $pluralModelLabel = 'Agents';

    protected static ?string $recordTitleAttribute = 'agency_name';

    public static function canAccess(): bool
    {
        return
            auth()
                ->user()
                ?->hasPermission('agents.view')
            ?? false;
    }

    public static function canViewAny(): bool
    {
        return static::canAccess();
    }

    public static function canView(Model $record): bool
    {
        return
            static::isAgent($record)
            && static::canAccess();
    }

    public static function canCreate(): bool
    {
        return
            auth()
                ->user()
                ?->hasPermission('agents.create')
            ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return
            static::isAgent($record)
            && (
                auth()
                    ->user()
                    ?->hasPermission('agents.edit')
                ?? false
            );
    }

    public static function canDelete(Model $record): bool
    {
        return
            static::isAgent($record)
            && (
                auth()
                    ->user()
                    ?->hasPermission('agents.delete')
                ?? false
            );
    }

    public static function canDeleteAny(): bool
    {
        return
            auth()
                ->user()
                ?->hasPermission('agents.delete')
            ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        return
            parent::getEloquentQuery()
                ->where(
                    'customer_type',
                    'Agent',
                );
    }

    public static function form(Schema $schema): Schema
    {
        return AgentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AgentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AgentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAgents::route('/'),
            'create' => CreateAgent::route('/create'),
            'view' => ViewAgent::route('/{record}'),
            'edit' => EditAgent::route('/{record}/edit'),
        ];
    }

    private static function isAgent(Model $record): bool
    {
        return
            $record instanceof Customer
            && $record->customer_type === 'Agent';
    }
}