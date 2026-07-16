<?php

namespace App\Filament\Resources\UmrahBookings;

use App\Filament\Resources\UmrahBookings\Pages\CreateUmrahBooking;
use App\Filament\Resources\UmrahBookings\Pages\EditUmrahBooking;
use App\Filament\Resources\UmrahBookings\Pages\ListUmrahBookings;
use App\Filament\Resources\UmrahBookings\Pages\ViewUmrahBooking;
use App\Filament\Resources\UmrahBookings\Schemas\UmrahBookingForm;
use App\Filament\Resources\UmrahBookings\Schemas\UmrahBookingInfolist;
use App\Filament\Resources\UmrahBookings\Tables\UmrahBookingsTable;
use App\Models\UmrahBooking;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UmrahBookingResource extends Resource
{

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-library';


    protected static ?string $model = UmrahBooking::class;



    protected static string | \UnitEnum | null $navigationGroup = 'Bookings';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'booking_no';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasPermission('umrah_bookings.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission('umrah_bookings.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasPermission('umrah_bookings.edit') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasPermission('umrah_bookings.delete') ?? false;
    }
public static function form(Schema $schema): Schema
    {
        return UmrahBookingForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UmrahBookingInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UmrahBookingsTable::configure($table);
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
            'index' => ListUmrahBookings::route('/'),
            'create' => CreateUmrahBooking::route('/create'),
            'view' => ViewUmrahBooking::route('/{record}'),
            'edit' => EditUmrahBooking::route('/{record}/edit'),
        ];
    }
}









