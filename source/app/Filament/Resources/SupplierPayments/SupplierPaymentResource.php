<?php

namespace App\Filament\Resources\SupplierPayments;

use App\Filament\Resources\SupplierPayments\Pages\CreateSupplierPayment;
use App\Filament\Resources\SupplierPayments\Pages\EditSupplierPayment;
use App\Filament\Resources\SupplierPayments\Pages\ListSupplierPayments;
use App\Filament\Resources\SupplierPayments\Pages\ViewSupplierPayment;
use App\Filament\Resources\SupplierPayments\Schemas\SupplierPaymentForm;
use App\Filament\Resources\SupplierPayments\Schemas\SupplierPaymentInfolist;
use App\Filament\Resources\SupplierPayments\Tables\SupplierPaymentsTable;
use App\Models\SupplierPayment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class SupplierPaymentResource extends Resource
{

    use \App\Filament\Concerns\HasRbacResourceAuthorization;
protected static ?string $model =
        SupplierPayment::class;

    protected static string|BackedEnum|null
        $navigationIcon = 'heroicon-o-banknotes';

    protected static string|UnitEnum|null
        $navigationGroup = 'Finance';

    protected static ?string $navigationLabel =
        'Supplier Payments';

    protected static ?string $modelLabel =
        'Supplier Payment';

    protected static ?string $pluralModelLabel =
        'Supplier Payments';

    protected static ?string $recordTitleAttribute =
        'payment_no';

    protected static ?int $navigationSort = 31;

    public static function form(
        Schema $schema
    ): Schema {
        return SupplierPaymentForm::configure(
            $schema
        );
    }

    public static function infolist(
        Schema $schema
    ): Schema {
        return SupplierPaymentInfolist::configure(
            $schema
        );
    }

    public static function table(
        Table $table
    ): Table {
        return SupplierPaymentsTable::configure(
            $table
        );
    }

    public static function getPages(): array
    {
        return [
            'index' =>
                ListSupplierPayments::route('/'),

            'create' =>
                CreateSupplierPayment::route(
                    '/create'
                ),

            'view' =>
                ViewSupplierPayment::route(
                    '/{record}'
                ),

            'edit' =>
                EditSupplierPayment::route(
                    '/{record}/edit'
                ),
        ];
    }
}