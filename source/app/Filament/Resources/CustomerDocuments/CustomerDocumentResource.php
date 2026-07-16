<?php

namespace App\Filament\Resources\CustomerDocuments;

use App\Filament\Resources\CustomerDocuments\Forms\CustomerDocumentForm;
use App\Filament\Resources\CustomerDocuments\Pages\CreateCustomerDocument;
use App\Filament\Resources\CustomerDocuments\Pages\EditCustomerDocument;
use App\Filament\Resources\CustomerDocuments\Pages\ListCustomerDocuments;
use App\Filament\Resources\CustomerDocuments\Pages\ViewCustomerDocument;
use App\Filament\Resources\CustomerDocuments\Tables\CustomerDocumentsTable;
use App\Models\CustomerDocument;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CustomerDocumentResource extends Resource
{

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-folder-open';


    protected static ?string $model = CustomerDocument::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Customers';
    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Customer Documents';
    protected static ?string $modelLabel = 'Customer Document';
    protected static ?string $pluralModelLabel = 'Customer Documents';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasPermission('customer_documents.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission('customer_documents.create') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->hasPermission('customer_documents.edit') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->hasPermission('customer_documents.delete') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return CustomerDocumentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerDocumentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomerDocuments::route('/'),
            'create' => CreateCustomerDocument::route('/create'),
            'view' => ViewCustomerDocument::route('/{record}'),
            'edit' => EditCustomerDocument::route('/{record}/edit'),
        ];
    }
}




