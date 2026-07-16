<?php

namespace App\Filament\Resources\StaffDocuments;

use App\Filament\Resources\StaffDocuments\Pages\CreateStaffDocument;
use App\Filament\Resources\StaffDocuments\Pages\EditStaffDocument;
use App\Filament\Resources\StaffDocuments\Pages\ListStaffDocuments;
use App\Filament\Resources\StaffDocuments\Pages\ViewStaffDocument;
use App\Filament\Resources\StaffDocuments\Schemas\StaffDocumentForm;
use App\Filament\Resources\StaffDocuments\Schemas\StaffDocumentInfolist;
use App\Filament\Resources\StaffDocuments\Tables\StaffDocumentsTable;
use App\Models\StaffDocument;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class StaffDocumentResource extends Resource
{
    protected static ?string $model = StaffDocument::class;

    protected static string|BackedEnum|null $navigationIcon =
        'heroicon-o-document-text';

    protected static string|UnitEnum|null $navigationGroup = 'HR';

    protected static ?string $navigationLabel = 'Staff Documents';

    protected static ?string $modelLabel = 'Staff Document';

    protected static ?string $pluralModelLabel = 'Staff Documents';

    protected static ?string $recordTitleAttribute = 'document_no';

    protected static ?int $navigationSort = 12;

    public static function form(Schema $schema): Schema
    {
        return StaffDocumentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return StaffDocumentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StaffDocumentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStaffDocuments::route('/'),
            'create' => CreateStaffDocument::route('/create'),
            'view' => ViewStaffDocument::route('/{record}'),
            'edit' => EditStaffDocument::route('/{record}/edit'),
        ];
    }
}