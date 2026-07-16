<?php

namespace App\Filament\Resources\HotelBookings;

use App\Filament\Resources\HotelBookings\Pages\CreateHotelBooking;
use App\Filament\Resources\HotelBookings\Pages\EditHotelBooking;
use App\Filament\Resources\HotelBookings\Pages\ListHotelBookings;
use App\Filament\Resources\HotelBookings\Pages\ViewHotelBooking;
use App\Filament\Resources\HotelBookings\Schemas\HotelBookingForm;
use App\Filament\Resources\HotelBookings\Schemas\HotelBookingInfolist;
use App\Filament\Resources\HotelBookings\Tables\HotelBookingsTable;
use App\Models\HotelBooking;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class HotelBookingResource extends Resource
{

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-office-2';


    protected static ?string $model = HotelBooking::class;



    protected static string | \UnitEnum | null $navigationGroup = 'Bookings';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'booking_no';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasPermission('hotel_bookings.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission('hotel_bookings.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasPermission('hotel_bookings.edit') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasPermission('hotel_bookings.delete') ?? false;
    }
public static function form(Schema $schema): Schema
    {
        return HotelBookingForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return HotelBookingInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HotelBookingsTable::configure($table);
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
            'index' => ListHotelBookings::route('/'),
            'create' => CreateHotelBooking::route('/create'),
            'view' => ViewHotelBooking::route('/{record}'),
            'edit' => EditHotelBooking::route('/{record}/edit'),
        ];
    }
}









