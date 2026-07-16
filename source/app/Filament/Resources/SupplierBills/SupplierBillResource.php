<?php

namespace App\Filament\Resources\SupplierBills;

use App\Filament\Resources\SupplierBills\Pages\CreateSupplierBill;
use App\Filament\Resources\SupplierBills\Pages\EditSupplierBill;
use App\Filament\Resources\SupplierBills\Pages\ListSupplierBills;
use App\Filament\Resources\SupplierBills\Pages\ViewSupplierBill;
use App\Filament\Resources\SupplierBills\Schemas\SupplierBillForm;
use App\Filament\Resources\SupplierBills\Schemas\SupplierBillInfolist;
use App\Filament\Resources\SupplierBills\Tables\SupplierBillsTable;
use App\Models\SupplierBill;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class SupplierBillResource extends Resource
{
    protected static ?string $model =
        SupplierBill::class;

    protected static string|BackedEnum|null
        $navigationIcon = 'heroicon-o-document-currency-dollar';

    protected static string|UnitEnum|null
        $navigationGroup = 'Finance';

    protected static ?string $navigationLabel =
        'Supplier Bills';

    protected static ?string $modelLabel =
        'Supplier Bill';

    protected static ?string $pluralModelLabel =
        'Supplier Bills';

    protected static ?string $recordTitleAttribute =
        'bill_no';

    protected static ?int $navigationSort = 30;

    public static function form(
        Schema $schema
    ): Schema {
        return SupplierBillForm::configure(
            $schema
        );
    }

    public static function infolist(
        Schema $schema
    ): Schema {
        return SupplierBillInfolist::configure(
            $schema
        );
    }

    public static function table(
        Table $table
    ): Table {
        return SupplierBillsTable::configure(
            $table
        );
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSupplierBills::route('/'),
            'create' => CreateSupplierBill::route('/create'),
            'view' => ViewSupplierBill::route('/{record}'),
            'edit' => EditSupplierBill::route('/{record}/edit'),
        ];
    }
}