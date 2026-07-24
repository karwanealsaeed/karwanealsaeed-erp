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
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-folder-open';

    protected static ?string $model = CustomerDocument::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Customers';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Customer Documents';

    protected static ?string $modelLabel = 'Customer Document';

    protected static ?string $pluralModelLabel = 'Customer Documents';

    private static function allowsDocumentAction(string $action): bool
    {
        $user = auth()->user();

        if ($user === null || ! (bool) $user->is_active) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $user->hasPermission(
            "customer_documents_{$action}",
        );
    }

    public static function canAccess(): bool
    {
        return static::canViewAny();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function canViewAny(): bool
    {
        return static::allowsDocumentAction('view');
    }

    public static function canView(Model $record): bool
    {
        return static::canViewAny();
    }

    public static function canCreate(): bool
    {
        return static::allowsDocumentAction('create');
    }

    public static function canEdit(Model $record): bool
    {
        return static::allowsDocumentAction('edit');
    }

    public static function canDelete(Model $record): bool
    {
        return static::allowsDocumentAction('delete');
    }

    public static function canDeleteAny(): bool
    {
        return static::allowsDocumentAction('delete');
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
