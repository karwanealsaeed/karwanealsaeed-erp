<?php

namespace App\Filament\Resources\VisaTypes;

use App\Filament\Resources\VisaTypes\Pages\CreateVisaType;
use App\Filament\Resources\VisaTypes\Pages\EditVisaType;
use App\Filament\Resources\VisaTypes\Pages\ListVisaTypes;
use App\Filament\Resources\VisaTypes\Pages\ViewVisaType;
use App\Filament\Resources\VisaTypes\Schemas\VisaTypeForm;
use App\Filament\Resources\VisaTypes\Schemas\VisaTypeInfolist;
use App\Filament\Resources\VisaTypes\Tables\VisaTypesTable;
use App\Models\VisaType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class VisaTypeResource extends Resource
{

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-identification';


    protected static ?string $model = VisaType::class;



    protected static string | \UnitEnum | null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'name';


    public static function canAccess(): bool
    {
        return auth()->user()?->hasPermission('visa_types.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission('visa_types.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasPermission('visa_types.edit') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasPermission('visa_types.delete') ?? false;
    }
    public static function form(Schema $schema): Schema
    {
        return VisaTypeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return VisaTypeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VisaTypesTable::configure($table);
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
            'index' => ListVisaTypes::route('/'),
            'create' => CreateVisaType::route('/create'),
            'view' => ViewVisaType::route('/{record}'),
            'edit' => EditVisaType::route('/{record}/edit'),
        ];
    }
}








