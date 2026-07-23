<?php

namespace App\Filament\Resources\Hotels;

use App\Filament\Resources\Hotels\Pages\CreateHotel;
use App\Filament\Resources\Hotels\Pages\EditHotel;
use App\Filament\Resources\Hotels\Pages\ListHotels;
use App\Filament\Resources\Hotels\Pages\ViewHotel;
use App\Filament\Resources\Hotels\Schemas\HotelForm;
use App\Filament\Resources\Hotels\Schemas\HotelInfolist;
use App\Filament\Resources\Hotels\Tables\HotelsTable;
use App\Models\Hotel;
use App\Filament\Concerns\HasRbacResourceAuthorization;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class HotelResource extends Resource
{
    use HasRbacResourceAuthorization;

    protected static ?string $model = Hotel::class;

    protected static string | \BackedEnum | null $navigationIcon =
        'heroicon-o-building-office-2';

    protected static string | \UnitEnum | null $navigationGroup =
        'Bookings';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel =
        'Hotel Master';

    protected static ?string $modelLabel =
        'Hotel';

    protected static ?string $pluralModelLabel =
        'Hotels';

    protected static ?string $recordTitleAttribute =
        'hotel_name';

    public static function form(Schema $schema): Schema
    {
        return HotelForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return HotelInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HotelsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHotels::route('/'),
            'create' => CreateHotel::route('/create'),
            'view' => ViewHotel::route('/{record}'),
            'edit' => EditHotel::route('/{record}/edit'),
        ];
    }
}