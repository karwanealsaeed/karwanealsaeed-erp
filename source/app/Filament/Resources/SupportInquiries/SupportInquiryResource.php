<?php

namespace App\Filament\Resources\SupportInquiries;

use App\Filament\Resources\SupportInquiries\Pages\CreateSupportInquiry;
use App\Filament\Resources\SupportInquiries\Pages\EditSupportInquiry;
use App\Filament\Resources\SupportInquiries\Pages\ListSupportInquiries;
use App\Filament\Resources\SupportInquiries\Pages\ViewSupportInquiry;
use App\Filament\Resources\SupportInquiries\Schemas\SupportInquiryForm;
use App\Filament\Resources\SupportInquiries\Schemas\SupportInquiryInfolist;
use App\Filament\Resources\SupportInquiries\Tables\SupportInquiriesTable;
use App\Models\SupportInquiry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SupportInquiryResource extends Resource
{
    protected static ?string $model = SupportInquiry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|UnitEnum|null $navigationGroup = 'Customers';

    protected static ?string $navigationLabel = 'Support Inquiries';

    protected static ?string $modelLabel = 'Support Inquiry';

    protected static ?string $pluralModelLabel = 'Support Inquiries';

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        return (auth()->user()?->isAdmin() ?? false)
            || (auth()->user()?->hasPermission('customers.view') ?? false);
    }

    public static function canCreate(): bool
    {
        return (auth()->user()?->isAdmin() ?? false)
            || (auth()->user()?->hasPermission('customers.create') ?? false);
    }

    public static function canEdit($record): bool
    {
        return (auth()->user()?->isAdmin() ?? false)
            || (auth()->user()?->hasPermission('customers.edit') ?? false);
    }

    public static function canDelete($record): bool
    {
        return (auth()->user()?->isAdmin() ?? false)
            || (auth()->user()?->hasPermission('customers.delete') ?? false);
    }

    public static function form(Schema $schema): Schema
    {
        return SupportInquiryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SupportInquiryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SupportInquiriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSupportInquiries::route('/'),
            'create' => CreateSupportInquiry::route('/create'),
            'view' => ViewSupportInquiry::route('/{record}'),
            'edit' => EditSupportInquiry::route('/{record}/edit'),
        ];
    }
}