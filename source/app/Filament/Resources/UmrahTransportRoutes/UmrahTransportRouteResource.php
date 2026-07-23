<?php

namespace App\Filament\Resources\UmrahTransportRoutes;

use App\Filament\Resources\UmrahTransportRoutes\Pages\CreateUmrahTransportRoute;
use App\Filament\Resources\UmrahTransportRoutes\Pages\EditUmrahTransportRoute;
use App\Filament\Resources\UmrahTransportRoutes\Pages\ListUmrahTransportRoutes;
use App\Filament\Resources\UmrahTransportRoutes\Pages\ViewUmrahTransportRoute;
use App\Filament\Resources\UmrahTransportRoutes\Schemas\UmrahTransportRouteForm;
use App\Filament\Resources\UmrahTransportRoutes\Tables\UmrahTransportRoutesTable;
use App\Models\UmrahTransportRoute;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class UmrahTransportRouteResource extends Resource
{
    use \App\Filament\Concerns\HasRbacResourceAuthorization;

    protected static ?string $model = UmrahTransportRoute::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-truck';

    protected static string | \UnitEnum | null $navigationGroup = 'Bookings';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Umrah Transport';

    protected static ?string $modelLabel = 'Umrah Transport';

    protected static ?string $pluralModelLabel = 'Umrah Transport';

    public static function form(Schema $schema): Schema
    {
        return UmrahTransportRouteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UmrahTransportRoutesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUmrahTransportRoutes::route('/'),
            'create' => CreateUmrahTransportRoute::route('/create'),
            'view' => ViewUmrahTransportRoute::route('/{record}'),
            'edit' => EditUmrahTransportRoute::route('/{record}/edit'),
        ];
    }
}