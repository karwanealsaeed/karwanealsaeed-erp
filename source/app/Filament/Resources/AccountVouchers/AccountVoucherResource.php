<?php

namespace App\Filament\Resources\AccountVouchers;

use App\Filament\Resources\AccountVouchers\Pages\CreateAccountVoucher;
use App\Filament\Resources\AccountVouchers\Pages\EditAccountVoucher;
use App\Filament\Resources\AccountVouchers\Pages\ListAccountVouchers;
use App\Filament\Resources\AccountVouchers\Pages\ViewAccountVoucher;
use App\Filament\Resources\AccountVouchers\Schemas\AccountVoucherForm;
use App\Filament\Resources\AccountVouchers\Schemas\AccountVoucherInfolist;
use App\Filament\Resources\AccountVouchers\Tables\AccountVouchersTable;
use App\Models\AccountVoucher;
use App\Support\RbacPermissionRegistry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AccountVoucherResource extends Resource
{
    protected static ?string $model = AccountVoucher::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCurrencyDollar;

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?string $navigationLabel = 'Accounting Vouchers';

    protected static ?string $modelLabel = 'Accounting Voucher';

    protected static ?string $pluralModelLabel = 'Accounting Vouchers';

    protected static ?int $navigationSort = 21;

    private static function allowsVoucherAction(string $action): bool
    {
        $user = auth()->user();

        if ($user === null || ! (bool) $user->is_active) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if (! in_array(
            $user->role,
            [
                RbacPermissionRegistry::ROLE_MANAGER,
                RbacPermissionRegistry::ROLE_ACCOUNTANT,
            ],
            true,
        )) {
            return false;
        }

        return $user->hasPermission(
            "account_vouchers_{$action}",
        ) || $user->hasPermission(
            RbacPermissionRegistry::ACCOUNTING_MANAGE,
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
        return static::allowsVoucherAction('view');
    }

    public static function canView($record): bool
    {
        return static::canViewAny();
    }

    public static function canCreate(): bool
    {
        return static::allowsVoucherAction('create');
    }

    public static function canEdit($record): bool
    {
        return static::allowsVoucherAction('edit')
            && $record->canBeManuallyChanged();
    }

    public static function canDelete($record): bool
    {
        return static::allowsVoucherAction('delete')
            && $record->canBeManuallyChanged();
    }

    public static function canDeleteAny(): bool
    {
        return static::allowsVoucherAction('delete');
    }

    public static function form(Schema $schema): Schema
    {
        return AccountVoucherForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AccountVoucherInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AccountVouchersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAccountVouchers::route('/'),
            'create' => CreateAccountVoucher::route('/create'),
            'view' => ViewAccountVoucher::route('/{record}'),
            'edit' => EditAccountVoucher::route('/{record}/edit'),
        ];
    }
}
