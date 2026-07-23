<?php

namespace App\Filament\Resources\VisaApplications;

use App\Filament\Resources\VisaApplications\Pages\CreateVisaApplication;
use App\Filament\Resources\VisaApplications\Pages\EditVisaApplication;
use App\Filament\Resources\VisaApplications\Pages\ListVisaApplications;
use App\Filament\Resources\VisaApplications\Pages\ViewVisaApplication;
use App\Filament\Resources\VisaApplications\Schemas\VisaApplicationForm;
use App\Filament\Resources\VisaApplications\Schemas\VisaApplicationInfolist;
use App\Filament\Resources\VisaApplications\Tables\VisaApplicationsTable;
use App\Models\VisaApplication;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class VisaApplicationResource extends Resource
{
    use \App\Filament\Concerns\HasRbacResourceAuthorization;


    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-check';


    protected static ?string $model = VisaApplication::class;


    protected static string | \UnitEnum | null $navigationGroup = 'Bookings';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'application_no';
public static function form(Schema $schema): Schema
    {
        return VisaApplicationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return VisaApplicationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VisaApplicationsTable::configure($table);
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
            'index' => ListVisaApplications::route('/'),
            'create' => CreateVisaApplication::route('/create'),
            'view' => ViewVisaApplication::route('/{record}'),
            'edit' => EditVisaApplication::route('/{record}/edit'),
        ];
    }
}


