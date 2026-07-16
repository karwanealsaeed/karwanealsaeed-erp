<?php

namespace App\Filament\Resources\Airports;

use App\Filament\Resources\Airports\Pages\CreateAirport;
use App\Filament\Resources\Airports\Pages\EditAirport;
use App\Filament\Resources\Airports\Pages\ListAirports;
use App\Filament\Resources\Airports\Pages\ViewAirport;
use App\Filament\Resources\Airports\Schemas\AirportForm;
use App\Filament\Resources\Airports\Schemas\AirportInfolist;
use App\Filament\Resources\Airports\Tables\AirportsTable;
use App\Models\Airport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AirportResource extends Resource
{

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-map-pin';


    protected static ?string $model = Airport::class;



    protected static string | \UnitEnum | null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';


    public static function canAccess(): bool
    {
        return auth()->user()?->hasPermission('airports.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission('airports.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasPermission('airports.edit') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasPermission('airports.delete') ?? false;
    }
    public static function form(Schema $schema): Schema
    {
        return AirportForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AirportInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AirportsTable::configure($table);
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
            'index' => ListAirports::route('/'),
            'create' => CreateAirport::route('/create'),
            'view' => ViewAirport::route('/{record}'),
            'edit' => EditAirport::route('/{record}/edit'),
        ];
    }
}








