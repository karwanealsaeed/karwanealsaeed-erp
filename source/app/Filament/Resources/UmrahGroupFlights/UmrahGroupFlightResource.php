<?php

namespace App\Filament\Resources\UmrahGroupFlights;

use App\Filament\Resources\UmrahGroupFlights\Pages\CreateUmrahGroupFlight;
use App\Filament\Resources\UmrahGroupFlights\Pages\EditUmrahGroupFlight;
use App\Filament\Resources\UmrahGroupFlights\Pages\ListUmrahGroupFlights;
use App\Filament\Resources\UmrahGroupFlights\Pages\ViewUmrahGroupFlight;
use App\Filament\Resources\UmrahGroupFlights\Schemas\UmrahGroupFlightForm;
use App\Filament\Resources\UmrahGroupFlights\Tables\UmrahGroupFlightsTable;
use App\Models\UmrahGroupFlight;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class UmrahGroupFlightResource extends Resource
{
    protected static ?string $model = UmrahGroupFlight::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-paper-airplane';

    protected static string | \UnitEnum | null $navigationGroup = 'Bookings';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Umrah Group Flights';

    protected static ?string $modelLabel = 'Umrah Group Flight';

    protected static ?string $pluralModelLabel = 'Umrah Group Flights';

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
        return UmrahGroupFlightForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UmrahGroupFlightsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUmrahGroupFlights::route('/'),
            'create' => CreateUmrahGroupFlight::route('/create'),
            'view' => ViewUmrahGroupFlight::route('/{record}'),
            'edit' => EditUmrahGroupFlight::route('/{record}/edit'),
        ];
    }
}