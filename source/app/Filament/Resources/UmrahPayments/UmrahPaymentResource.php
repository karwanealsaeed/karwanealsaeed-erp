<?php

namespace App\Filament\Resources\UmrahPayments;

use App\Filament\Resources\UmrahPayments\Pages\CreateUmrahPayment;
use App\Filament\Resources\UmrahPayments\Pages\EditUmrahPayment;
use App\Filament\Resources\UmrahPayments\Pages\ListUmrahPayments;
use App\Filament\Resources\UmrahPayments\Pages\ViewUmrahPayment;
use App\Filament\Resources\UmrahPayments\Schemas\UmrahPaymentForm;
use App\Filament\Resources\UmrahPayments\Schemas\UmrahPaymentInfolist;
use App\Filament\Resources\UmrahPayments\Tables\UmrahPaymentsTable;
use App\Models\UmrahPayment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UmrahPaymentResource extends Resource
{

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-credit-card';


    protected static ?string $model = UmrahPayment::class;



    protected static string | \UnitEnum | null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'reference_no';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasPermission('umrah_payments.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission('umrah_payments.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasPermission('umrah_payments.edit') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasPermission('umrah_payments.delete') ?? false;
    }
public static function form(Schema $schema): Schema
    {
        return UmrahPaymentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UmrahPaymentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UmrahPaymentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUmrahPayments::route('/'),
            'create' => CreateUmrahPayment::route('/create'),
            'view' => ViewUmrahPayment::route('/{record}'),
            'edit' => EditUmrahPayment::route('/{record}/edit'),
        ];
    }
}










