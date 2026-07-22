<?php

namespace App\Filament\Resources\Designations;

use App\Filament\Resources\Designations\Pages\CreateDesignation;
use App\Filament\Resources\Designations\Pages\EditDesignation;
use App\Filament\Resources\Designations\Pages\ListDesignations;
use App\Filament\Resources\Designations\Pages\ViewDesignation;
use App\Filament\Resources\Designations\Schemas\DesignationForm;
use App\Filament\Resources\Designations\Schemas\DesignationInfolist;
use App\Filament\Resources\Designations\Tables\DesignationsTable;
use App\Models\Designation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class DesignationResource extends Resource
{

    use \App\Filament\Concerns\HasRbacResourceAuthorization;
protected static ?string $model = Designation::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-identification';

    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Designations';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 81;

    public static function form(Schema $schema): Schema
    {
        return DesignationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DesignationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DesignationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDesignations::route('/'),
            'create' => CreateDesignation::route('/create'),
            'view' => ViewDesignation::route('/{record}'),
            'edit' => EditDesignation::route('/{record}/edit'),
        ];
    }
}