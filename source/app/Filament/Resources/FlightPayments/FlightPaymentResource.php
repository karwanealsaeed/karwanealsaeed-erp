<?php

namespace App\Filament\Resources\FlightPayments;

use App\Filament\Resources\FlightPayments\Pages\CreateFlightPayment;
use App\Filament\Resources\FlightPayments\Pages\EditFlightPayment;
use App\Filament\Resources\FlightPayments\Pages\ListFlightPayments;
use App\Filament\Resources\FlightPayments\Pages\ViewFlightPayment;
use App\Filament\Resources\FlightPayments\Schemas\FlightPaymentForm;
use App\Filament\Resources\FlightPayments\Schemas\FlightPaymentInfolist;
use App\Filament\Resources\FlightPayments\Tables\FlightPaymentsTable;
use App\Models\FlightPayment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FlightPaymentResource extends Resource
{
    use \App\Filament\Concerns\HasRbacResourceAuthorization;


    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-credit-card';


    protected static ?string $model = FlightPayment::class;


    protected static string | \UnitEnum | null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'reference_no';
public static function form(Schema $schema): Schema
    {
        return FlightPaymentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FlightPaymentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FlightPaymentsTable::configure($table);
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
            'index' => ListFlightPayments::route('/'),
            'create' => CreateFlightPayment::route('/create'),
            'view' => ViewFlightPayment::route('/{record}'),
            'edit' => EditFlightPayment::route('/{record}/edit'),
        ];
    }
}


