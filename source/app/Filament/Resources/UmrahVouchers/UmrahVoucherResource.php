<?php

namespace App\Filament\Resources\UmrahVouchers;

use App\Filament\Resources\UmrahVouchers\Pages\CreateUmrahVoucher;
use App\Filament\Resources\UmrahVouchers\Pages\EditUmrahVoucher;
use App\Filament\Resources\UmrahVouchers\Pages\ListUmrahVouchers;
use App\Filament\Resources\UmrahVouchers\Pages\ViewUmrahVoucher;
use App\Filament\Resources\UmrahVouchers\Schemas\UmrahVoucherForm;
use App\Filament\Resources\UmrahVouchers\Tables\UmrahVouchersTable;
use App\Models\UmrahVoucher;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class UmrahVoucherResource extends Resource
{
    use \App\Filament\Concerns\HasRbacResourceAuthorization;

    protected static ?string $model = UmrahVoucher::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-check';

    protected static string | \UnitEnum | null $navigationGroup = 'Bookings';

    protected static ?int $navigationSort = 7;

    protected static ?string $navigationLabel = 'Umrah Vouchers';

    protected static ?string $modelLabel = 'Umrah Voucher';

    protected static ?string $pluralModelLabel = 'Umrah Vouchers';

    public static function form(Schema $schema): Schema
    {
        return UmrahVoucherForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UmrahVouchersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUmrahVouchers::route('/'),
            'create' => CreateUmrahVoucher::route('/create'),
            'view' => ViewUmrahVoucher::route('/{record}'),
            'edit' => EditUmrahVoucher::route('/{record}/edit'),
        ];
    }
}