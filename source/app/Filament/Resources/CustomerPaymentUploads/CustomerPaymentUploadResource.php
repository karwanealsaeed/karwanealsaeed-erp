<?php

namespace App\Filament\Resources\CustomerPaymentUploads;

use App\Filament\Resources\CustomerPaymentUploads\Pages\CreateCustomerPaymentUpload;
use App\Filament\Resources\CustomerPaymentUploads\Pages\EditCustomerPaymentUpload;
use App\Filament\Resources\CustomerPaymentUploads\Pages\ListCustomerPaymentUploads;
use App\Filament\Resources\CustomerPaymentUploads\Pages\ViewCustomerPaymentUpload;
use App\Filament\Resources\CustomerPaymentUploads\Schemas\CustomerPaymentUploadForm;
use App\Filament\Resources\CustomerPaymentUploads\Schemas\CustomerPaymentUploadInfolist;
use App\Filament\Resources\CustomerPaymentUploads\Tables\CustomerPaymentUploadsTable;
use App\Models\CustomerPaymentUpload;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class CustomerPaymentUploadResource extends Resource
{
    protected static ?string $model = CustomerPaymentUpload::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|UnitEnum|null $navigationGroup = 'Customers';

    protected static ?string $navigationLabel = 'Payment Uploads';

    protected static ?string $modelLabel = 'Payment Upload';

    protected static ?string $pluralModelLabel = 'Payment Uploads';

    protected static ?int $navigationSort = 5;

    private static function allowsCustomerAction(string $action): bool
    {
        $user = auth()->user();

        if ($user === null || ! (bool) $user->is_active) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $user->hasPermission(
            "customers_{$action}",
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
        return static::allowsCustomerAction('view');
    }

    public static function canView($record): bool
    {
        return static::canViewAny();
    }

    public static function canCreate(): bool
    {
        return static::allowsCustomerAction('create');
    }

    public static function canEdit($record): bool
    {
        return static::allowsCustomerAction('edit');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return CustomerPaymentUploadForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CustomerPaymentUploadInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerPaymentUploadsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomerPaymentUploads::route('/'),
            'create' => CreateCustomerPaymentUpload::route('/create'),
            'view' => ViewCustomerPaymentUpload::route('/{record}'),
            'edit' => EditCustomerPaymentUpload::route('/{record}/edit'),
        ];
    }
}
