<?php

namespace App\Filament\Resources\FlightBookings;

use App\Filament\Resources\FlightBookings\Pages\CreateFlightBooking;
use App\Filament\Resources\FlightBookings\Pages\EditFlightBooking;
use App\Filament\Resources\FlightBookings\Pages\ListFlightBookings;
use App\Filament\Resources\FlightBookings\Pages\ViewFlightBooking;
use App\Filament\Resources\FlightBookings\Schemas\FlightBookingForm;
use App\Filament\Resources\FlightBookings\Schemas\FlightBookingInfolist;
use App\Filament\Resources\FlightBookings\Tables\FlightBookingsTable;
use App\Models\FlightBooking;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FlightBookingResource extends Resource
{

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-paper-airplane';


    protected static ?string $model = FlightBooking::class;



    protected static string | \UnitEnum | null $navigationGroup = 'Bookings';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'booking_no';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasPermission('flight_bookings.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission('flight_bookings.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasPermission('flight_bookings.edit') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasPermission('flight_bookings.delete') ?? false;
    }
public static function form(Schema $schema): Schema
    {
        return FlightBookingForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FlightBookingInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FlightBookingsTable::configure($table);
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
            'index' => ListFlightBookings::route('/'),
            'create' => CreateFlightBooking::route('/create'),
            'view' => ViewFlightBooking::route('/{record}'),
            'edit' => EditFlightBooking::route('/{record}/edit'),
        ];
    }
}









