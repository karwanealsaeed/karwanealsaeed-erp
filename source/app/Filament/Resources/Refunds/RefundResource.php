<?php

namespace App\Filament\Resources\Refunds;

use App\Filament\Resources\Refunds\Pages\CreateRefund;
use App\Filament\Resources\Refunds\Pages\EditRefund;
use App\Filament\Resources\Refunds\Pages\ListRefunds;
use App\Filament\Resources\Refunds\Pages\ViewRefund;
use App\Filament\Resources\Refunds\Schemas\RefundForm;
use App\Filament\Resources\Refunds\Schemas\RefundInfolist;
use App\Filament\Resources\Refunds\Tables\RefundsTable;
use App\Models\Refund;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class RefundResource extends Resource
{
    protected static ?string $model = Refund::class;

    protected static string|BackedEnum|null $navigationIcon =
        'heroicon-o-arrow-uturn-left';

    protected static string|UnitEnum|null $navigationGroup =
        'Finance';

    protected static ?int $navigationSort = 8;

    protected static ?string $navigationLabel =
        'Refunds';

    protected static ?string $modelLabel =
        'Refund';

    protected static ?string $pluralModelLabel =
        'Refunds';

    protected static ?string $recordTitleAttribute =
        'refund_no';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasPermission(
            'refunds.view'
        ) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission(
            'refunds.create'
        ) ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasPermission(
            'refunds.edit'
        ) ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasPermission(
            'refunds.delete'
        ) ?? false;
    }

    public static function form(
        Schema $schema
    ): Schema {
        return RefundForm::configure($schema);
    }

    public static function infolist(
        Schema $schema
    ): Schema {
        return RefundInfolist::configure($schema);
    }

    public static function table(
        Table $table
    ): Table {
        return RefundsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRefunds::route('/'),

            'create' => CreateRefund::route(
                '/create'
            ),

            'view' => ViewRefund::route(
                '/{record}'
            ),

            'edit' => EditRefund::route(
                '/{record}/edit'
            ),
        ];
    }
}