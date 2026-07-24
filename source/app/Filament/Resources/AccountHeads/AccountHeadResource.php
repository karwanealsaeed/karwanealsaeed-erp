<?php

namespace App\Filament\Resources\AccountHeads;

use App\Filament\Concerns\HasRbacResourceAuthorization;
use App\Filament\Resources\AccountHeads\Pages\CreateAccountHead;
use App\Filament\Resources\AccountHeads\Pages\EditAccountHead;
use App\Filament\Resources\AccountHeads\Pages\ListAccountHeads;
use App\Filament\Resources\AccountHeads\Pages\ViewAccountHead;
use App\Filament\Resources\AccountHeads\Schemas\AccountHeadForm;
use App\Filament\Resources\AccountHeads\Schemas\AccountHeadInfolist;
use App\Filament\Resources\AccountHeads\Tables\AccountHeadsTable;
use App\Models\AccountHead;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AccountHeadResource extends Resource
{
    use HasRbacResourceAuthorization;

    protected static ?string $model = AccountHead::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?string $navigationLabel = 'Chart of Accounts';

    protected static ?string $modelLabel = 'Account Head';

    protected static ?string $pluralModelLabel = 'Chart of Accounts';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return AccountHeadForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AccountHeadInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AccountHeadsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAccountHeads::route('/'),
            'create' => CreateAccountHead::route('/create'),
            'view' => ViewAccountHead::route('/{record}'),
            'edit' => EditAccountHead::route('/{record}/edit'),
        ];
    }
}
