<?php

namespace App\Filament\Resources\UmrahPackages;

use App\Filament\Resources\UmrahPackages\Pages\CreateUmrahPackage;
use App\Filament\Resources\UmrahPackages\Pages\EditUmrahPackage;
use App\Filament\Resources\UmrahPackages\Pages\ListUmrahPackages;
use App\Filament\Resources\UmrahPackages\Pages\ViewUmrahPackage;
use App\Filament\Resources\UmrahPackages\Schemas\UmrahPackageForm;
use App\Filament\Resources\UmrahPackages\Tables\UmrahPackagesTable;
use App\Models\UmrahPackage;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class UmrahPackageResource extends Resource
{
    protected static ?string $model = UmrahPackage::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-library';

    protected static string | \UnitEnum | null $navigationGroup = 'Bookings';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Umrah Packages';

    protected static ?string $modelLabel = 'Umrah Package';

    protected static ?string $pluralModelLabel = 'Umrah Packages';

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
        return UmrahPackageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UmrahPackagesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUmrahPackages::route('/'),
            'create' => CreateUmrahPackage::route('/create'),
            'view' => ViewUmrahPackage::route('/{record}'),
            'edit' => EditUmrahPackage::route('/{record}/edit'),
        ];
    }
}