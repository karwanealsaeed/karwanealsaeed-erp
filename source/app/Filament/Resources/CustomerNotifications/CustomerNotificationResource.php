<?php

namespace App\Filament\Resources\CustomerNotifications;

use App\Filament\Resources\CustomerNotifications\Pages\CreateCustomerNotification;
use App\Filament\Resources\CustomerNotifications\Pages\EditCustomerNotification;
use App\Filament\Resources\CustomerNotifications\Pages\ListCustomerNotifications;
use App\Filament\Resources\CustomerNotifications\Pages\ViewCustomerNotification;
use App\Filament\Resources\CustomerNotifications\Schemas\CustomerNotificationForm;
use App\Filament\Resources\CustomerNotifications\Schemas\CustomerNotificationInfolist;
use App\Filament\Resources\CustomerNotifications\Tables\CustomerNotificationsTable;
use App\Models\CustomerNotification;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class CustomerNotificationResource extends Resource
{
    protected static ?string $model = CustomerNotification::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-bell-alert';

    protected static string|UnitEnum|null $navigationGroup = 'Customers';

    protected static ?string $navigationLabel = 'Notifications & Offers';

    protected static ?string $modelLabel = 'Customer Notification';

    protected static ?string $pluralModelLabel = 'Notifications & Offers';

    protected static ?int $navigationSort = 6;

    private static function allowsNotificationAction(string $action): bool
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
        return static::allowsNotificationAction('view');
    }

    public static function canView($record): bool
    {
        return static::canViewAny();
    }

    public static function canCreate(): bool
    {
        return static::allowsNotificationAction('create');
    }

    public static function canEdit($record): bool
    {
        return static::allowsNotificationAction('edit');
    }

    public static function canDelete($record): bool
    {
        return static::allowsNotificationAction('delete');
    }

    public static function canDeleteAny(): bool
    {
        return static::allowsNotificationAction('delete');
    }

    public static function form(Schema $schema): Schema
    {
        return CustomerNotificationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CustomerNotificationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerNotificationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomerNotifications::route('/'),
            'create' => CreateCustomerNotification::route('/create'),
            'view' => ViewCustomerNotification::route('/{record}'),
            'edit' => EditCustomerNotification::route('/{record}/edit'),
        ];
    }
}
