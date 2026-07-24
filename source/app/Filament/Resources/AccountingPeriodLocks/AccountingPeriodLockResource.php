<?php

namespace App\Filament\Resources\AccountingPeriodLocks;

use App\Filament\Concerns\HasRbacResourceAuthorization;
use App\Filament\Resources\AccountingPeriodLocks\Pages\CreateAccountingPeriodLock;
use App\Filament\Resources\AccountingPeriodLocks\Pages\EditAccountingPeriodLock;
use App\Filament\Resources\AccountingPeriodLocks\Pages\ListAccountingPeriodLocks;
use App\Filament\Resources\AccountingPeriodLocks\Pages\ViewAccountingPeriodLock;
use App\Filament\Resources\AccountingPeriodLocks\Schemas\AccountingPeriodLockForm;
use App\Filament\Resources\AccountingPeriodLocks\Schemas\AccountingPeriodLockInfolist;
use App\Filament\Resources\AccountingPeriodLocks\Tables\AccountingPeriodLocksTable;
use App\Models\AccountingPeriodLock;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class AccountingPeriodLockResource extends Resource
{
    use HasRbacResourceAuthorization;

    public static function canAccess(): bool
    {
        return static::canViewAny();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    protected static ?string $model =
        AccountingPeriodLock::class;

    protected static string|BackedEnum|null $navigationIcon =
        'heroicon-o-lock-closed';

    protected static string|UnitEnum|null $navigationGroup =
        'Finance';

    protected static ?string $navigationLabel =
        'Accounting Period Locks';

    protected static ?string $modelLabel =
        'Accounting Period';

    protected static ?string $pluralModelLabel =
        'Accounting Period Locks';

    protected static ?string $recordTitleAttribute =
        'name';

    protected static ?int $navigationSort = 30;

    public static function canViewPeriods(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        foreach (
            [
                'accounting_period_locks.view',
                'accounting.view',
                'reports.view',
            ] as $permission
        ) {
            if ($user->hasPermission($permission)) {
                return true;
            }
        }

        return static::isAdministrativeUser($user);
    }

    public static function canManagePeriods(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        foreach (
            [
                'accounting_period_locks.manage',
                'accounting.manage',
                'settings.manage',
            ] as $permission
        ) {
            if ($user->hasPermission($permission)) {
                return true;
            }
        }

        return static::isAdministrativeUser($user);
    }

    public static function form(
        Schema $schema
    ): Schema {
        return AccountingPeriodLockForm::configure(
            $schema
        );
    }

    public static function infolist(
        Schema $schema
    ): Schema {
        return AccountingPeriodLockInfolist::configure(
            $schema
        );
    }

    public static function table(
        Table $table
    ): Table {
        return AccountingPeriodLocksTable::configure(
            $table
        );
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAccountingPeriodLocks::route('/'),

            'create' => CreateAccountingPeriodLock::route(
                '/create'
            ),

            'view' => ViewAccountingPeriodLock::route(
                '/{record}'
            ),

            'edit' => EditAccountingPeriodLock::route(
                '/{record}/edit'
            ),
        ];
    }

    private static function isAdministrativeUser(
        object $user
    ): bool {
        if ((bool) ($user->is_admin ?? false)) {
            return true;
        }

        $role = $user->role ?? null;

        $roleName = is_object($role)
            ? ($role->name ?? '')
            : $role;

        return in_array(
            strtolower(trim((string) $roleName)),
            [
                'admin',
                'administrator',
                'super admin',
                'super_admin',
            ],
            true
        );
    }
}
