<?php

namespace App\Filament\Resources\PakistanTours;

use App\Filament\Resources\PakistanTours\Pages\CreatePakistanTour;
use App\Filament\Resources\PakistanTours\Pages\EditPakistanTour;
use App\Filament\Resources\PakistanTours\Pages\ListPakistanTours;
use App\Filament\Resources\PakistanTours\Pages\ViewPakistanTour;
use App\Filament\Resources\PakistanTours\Schemas\PakistanTourForm;
use App\Filament\Resources\PakistanTours\Schemas\PakistanTourInfolist;
use App\Filament\Resources\PakistanTours\Tables\PakistanToursTable;
use App\Models\PakistanTour;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PakistanTourResource extends Resource
{
    protected static ?string $model = PakistanTour::class;

    protected static string|\BackedEnum|null $navigationIcon =
        'heroicon-o-map';

    protected static string|\UnitEnum|null $navigationGroup =
        'Website Content';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'Pakistan Tours';

    protected static ?string $modelLabel = 'Pakistan Tour';

    protected static ?string $pluralModelLabel = 'Pakistan Tours';

    protected static ?string $recordTitleAttribute = 'title';

    public static function canAccess(): bool
    {
        return (auth()->user()?->isAdmin() ?? false)
            || (
                auth()->user()?->hasPermission(
                    'pakistan_tours.view',
                ) ?? false
            );
    }

    public static function canCreate(): bool
    {
        return (auth()->user()?->isAdmin() ?? false)
            || (
                auth()->user()?->hasPermission(
                    'pakistan_tours.create',
                ) ?? false
            );
    }

    public static function canEdit(Model $record): bool
    {
        return (auth()->user()?->isAdmin() ?? false)
            || (
                auth()->user()?->hasPermission(
                    'pakistan_tours.edit',
                ) ?? false
            );
    }

    public static function canDelete(Model $record): bool
    {
        return (auth()->user()?->isAdmin() ?? false)
            || (
                auth()->user()?->hasPermission(
                    'pakistan_tours.delete',
                ) ?? false
            );
    }

    public static function canDeleteAny(): bool
    {
        return (auth()->user()?->isAdmin() ?? false)
            || (
                auth()->user()?->hasPermission(
                    'pakistan_tours.delete',
                ) ?? false
            );
    }

    public static function form(Schema $schema): Schema
    {
        return PakistanTourForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PakistanTourInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PakistanToursTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPakistanTours::route('/'),
            'create' => CreatePakistanTour::route('/create'),
            'view' => ViewPakistanTour::route('/{record}'),
            'edit' => EditPakistanTour::route('/{record}/edit'),
        ];
    }
}