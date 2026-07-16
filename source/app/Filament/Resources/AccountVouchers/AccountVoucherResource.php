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

    public static function canViewAny(): bool
    {
        return (auth()->user()?->isAdmin() ?? false)
            || (auth()->user()?->hasPermission('reports_view') ?? false);
    }

    public static function canEdit($record): bool
    {
        $user = auth()->user();

        $hasPermission =
            ($user?->isAdmin() ?? false)
            || (
                $user?->hasPermission(
                    'account_vouchers.edit'
                )
                ?? false
            )
            || (
                $user?->hasPermission(
                    'accounting.manage'
                )
                ?? false
            );

        return $hasPermission
            && $record->canBeManuallyChanged();
    }

    public static function canDelete($record): bool
    {
        $user = auth()->user();

        $hasPermission =
            ($user?->isAdmin() ?? false)
            || (
                $user?->hasPermission(
                    'account_vouchers.delete'
                )
                ?? false
            )
            || (
                $user?->hasPermission(
                    'accounting.manage'
                )
                ?? false
            );

        return $hasPermission
            && $record->canBeManuallyChanged();
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