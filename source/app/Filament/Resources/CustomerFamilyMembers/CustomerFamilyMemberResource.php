<?php

namespace App\Filament\Resources\CustomerFamilyMembers;

use App\Filament\Resources\CustomerFamilyMembers\Pages\CreateCustomerFamilyMember;
use App\Filament\Resources\CustomerFamilyMembers\Pages\EditCustomerFamilyMember;
use App\Filament\Resources\CustomerFamilyMembers\Pages\ListCustomerFamilyMembers;
use App\Filament\Resources\CustomerFamilyMembers\Pages\ViewCustomerFamilyMember;
use App\Filament\Resources\CustomerFamilyMembers\Schemas\CustomerFamilyMemberForm;
use App\Filament\Resources\CustomerFamilyMembers\Schemas\CustomerFamilyMemberInfolist;
use App\Filament\Resources\CustomerFamilyMembers\Tables\CustomerFamilyMembersTable;
use App\Models\CustomerFamilyMember;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class CustomerFamilyMemberResource extends Resource
{
    protected static ?string $model = CustomerFamilyMember::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static string|UnitEnum|null $navigationGroup = 'Customers';

    protected static ?string $navigationLabel = 'Family Members';

    protected static ?int $navigationSort = 8;

    private static function allowsFamilyAction(string $action): bool
    {
        $user = auth()->user();

        if ($user === null || ! (bool) $user->is_active) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $user->hasPermission(
            "customers_{$action}",
        );
    }

    public static function canAccess(): bool
    {
        return static::canViewAny();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function canViewAny(): bool
    {
        return static::allowsFamilyAction('view');
    }

    public static function canView($record): bool
    {
        return static::canViewAny();
    }

    public static function canCreate(): bool
    {
        return static::allowsFamilyAction('create');
    }

    public static function canEdit($record): bool
    {
        return static::allowsFamilyAction('edit');
    }

    public static function canDelete($record): bool
    {
        return static::allowsFamilyAction('delete');
    }

    public static function canDeleteAny(): bool
    {
        return static::allowsFamilyAction('delete');
    }

    public static function form(Schema $schema): Schema
    {
        return CustomerFamilyMemberForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CustomerFamilyMemberInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerFamilyMembersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomerFamilyMembers::route('/'),
            'create' => CreateCustomerFamilyMember::route('/create'),
            'view' => ViewCustomerFamilyMember::route('/{record}'),
            'edit' => EditCustomerFamilyMember::route('/{record}/edit'),
        ];
    }
}
