<?php

namespace App\Filament\Resources\Countries;

use App\Filament\Resources\Countries\Pages\CreateCountry;
use App\Filament\Resources\Countries\Pages\EditCountry;
use App\Filament\Resources\Countries\Pages\ListCountries;
use App\Filament\Resources\Countries\Pages\ViewCountry;
use App\Filament\Resources\Countries\Schemas\CountryForm;
use App\Filament\Resources\Countries\Schemas\CountryInfolist;
use App\Filament\Resources\Countries\Tables\CountriesTable;
use App\Models\Country;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CountryResource extends Resource
{

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-globe-alt';


    protected static ?string $model = Country::class;



    protected static string | \UnitEnum | null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';


    public static function canAccess(): bool
    {
        return auth()->user()?->hasPermission('countries.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission('countries.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasPermission('countries.edit') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasPermission('countries.delete') ?? false;
    }
    public static function form(Schema $schema): Schema
    {
        return CountryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CountryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CountriesTable::configure($table);
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
            'index' => ListCountries::route('/'),
            'create' => CreateCountry::route('/create'),
            'view' => ViewCountry::route('/{record}'),
            'edit' => EditCountry::route('/{record}/edit'),
        ];
    }
}








