<?php

namespace App\Filament\Resources\HotelPayments;

use App\Filament\Resources\HotelPayments\Pages\CreateHotelPayment;
use App\Filament\Resources\HotelPayments\Pages\EditHotelPayment;
use App\Filament\Resources\HotelPayments\Pages\ListHotelPayments;
use App\Filament\Resources\HotelPayments\Pages\ViewHotelPayment;
use App\Filament\Resources\HotelPayments\Schemas\HotelPaymentForm;
use App\Filament\Resources\HotelPayments\Schemas\HotelPaymentInfolist;
use App\Filament\Resources\HotelPayments\Tables\HotelPaymentsTable;
use App\Models\HotelPayment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class HotelPaymentResource extends Resource
{

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-credit-card';


    protected static ?string $model = HotelPayment::class;



    protected static string | \UnitEnum | null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'reference_no';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasPermission('hotel_payments.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission('hotel_payments.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasPermission('hotel_payments.edit') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasPermission('hotel_payments.delete') ?? false;
    }
public static function form(Schema $schema): Schema
    {
        return HotelPaymentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return HotelPaymentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HotelPaymentsTable::configure($table);
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
            'index' => ListHotelPayments::route('/'),
            'create' => CreateHotelPayment::route('/create'),
            'view' => ViewHotelPayment::route('/{record}'),
            'edit' => EditHotelPayment::route('/{record}/edit'),
        ];
    }
}










