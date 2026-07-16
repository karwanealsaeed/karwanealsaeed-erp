<?php

namespace App\Filament\Resources\UmrahHotels;

use App\Filament\Resources\UmrahHotels\Pages\CreateUmrahHotel;
use App\Filament\Resources\UmrahHotels\Pages\EditUmrahHotel;
use App\Filament\Resources\UmrahHotels\Pages\ListUmrahHotels;
use App\Filament\Resources\UmrahHotels\Pages\ViewUmrahHotel;
use App\Filament\Resources\UmrahHotels\Schemas\UmrahHotelForm;
use App\Filament\Resources\UmrahHotels\Tables\UmrahHotelsTable;
use App\Models\UmrahHotel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class UmrahHotelResource extends Resource
{
    protected static ?string $model = UmrahHotel::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-office-2';

    protected static string | \UnitEnum | null $navigationGroup = 'Bookings';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Umrah Hotels';

    protected static ?string $modelLabel = 'Umrah Hotel';

    protected static ?string $pluralModelLabel = 'Umrah Hotels';

    public static function canAccess(): bool
    {
        return (auth()->user()?->isAdmin() ?? false)
            || (auth()->user()?->hasPermission('umrah_bookings.view') ?? false);
    }

    public static function canCreate(): bool
    {
        return (auth()->user()?->isAdmin() ?? false)
            || (auth()->user()?->hasPermission('umrah_bookings.create') ?? false);
    }

    public static function canEdit(Model $record): bool
    {
        return (auth()->user()?->isAdmin() ?? false)
            || (auth()->user()?->hasPermission('umrah_bookings.edit') ?? false);
    }

    public static function canDelete(Model $record): bool
    {
        return (auth()->user()?->isAdmin() ?? false)
            || (auth()->user()?->hasPermission('umrah_bookings.delete') ?? false);
    }

    public static function form(Schema $schema): Schema
    {
        return UmrahHotelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UmrahHotelsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUmrahHotels::route('/'),
            'create' => CreateUmrahHotel::route('/create'),
            'view' => ViewUmrahHotel::route('/{record}'),
            'edit' => EditUmrahHotel::route('/{record}/edit'),
        ];
    }
}