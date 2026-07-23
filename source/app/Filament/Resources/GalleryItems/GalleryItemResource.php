<?php

namespace App\Filament\Resources\GalleryItems;

use App\Filament\Resources\GalleryItems\Pages\CreateGalleryItem;
use App\Filament\Resources\GalleryItems\Pages\EditGalleryItem;
use App\Filament\Resources\GalleryItems\Pages\ListGalleryItems;
use App\Filament\Resources\GalleryItems\Pages\ViewGalleryItem;
use App\Filament\Resources\GalleryItems\Schemas\GalleryItemForm;
use App\Filament\Resources\GalleryItems\Schemas\GalleryItemInfolist;
use App\Filament\Resources\GalleryItems\Tables\GalleryItemsTable;
use App\Models\GalleryItem;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class GalleryItemResource extends Resource
{
    use \App\Filament\Concerns\HasRbacResourceAuthorization;

    protected static ?string $model = GalleryItem::class;

    protected static string|\BackedEnum|null $navigationIcon =
        'heroicon-o-photo';

    protected static string|\UnitEnum|null $navigationGroup =
        'Website Content';

    protected static ?int $navigationSort = 20;

    protected static ?string $navigationLabel = 'Gallery';

    protected static ?string $modelLabel = 'Gallery Item';

    protected static ?string $pluralModelLabel = 'Gallery';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return GalleryItemForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return GalleryItemInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GalleryItemsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGalleryItems::route('/'),
            'create' => CreateGalleryItem::route('/create'),
            'view' => ViewGalleryItem::route('/{record}'),
            'edit' => EditGalleryItem::route('/{record}/edit'),
        ];
    }
}