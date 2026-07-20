<?php

namespace App\Filament\Resources\Promotions;

use App\Filament\Resources\Promotions\Pages\CreatePromotion;
use App\Filament\Resources\Promotions\Pages\EditPromotion;
use App\Filament\Resources\Promotions\Pages\ListPromotions;
use App\Filament\Resources\Promotions\Pages\ViewPromotion;
use App\Filament\Resources\Promotions\Schemas\PromotionForm;
use App\Filament\Resources\Promotions\Schemas\PromotionInfolist;
use App\Filament\Resources\Promotions\Tables\PromotionsTable;
use App\Models\Promotion;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PromotionResource extends Resource
{
    protected static ?string $model = Promotion::class;

    protected static string|\BackedEnum|null $navigationIcon =
        'heroicon-o-megaphone';

    protected static string|\UnitEnum|null $navigationGroup =
        'Website Content';

    protected static ?int $navigationSort = 60;

    protected static ?string $navigationLabel =
        'Promotions';

    protected static ?string $modelLabel =
        'Promotion';

    protected static ?string $pluralModelLabel =
        'Promotions';

    protected static ?string $recordTitleAttribute =
        'title';

    public static function canAccess(): bool
    {
        return (auth()->user()?->isAdmin() ?? false)
            || (
                auth()->user()?->hasPermission(
                    'promotions.view',
                ) ?? false
            );
    }

    public static function canCreate(): bool
    {
        return (auth()->user()?->isAdmin() ?? false)
            || (
                auth()->user()?->hasPermission(
                    'promotions.create',
                ) ?? false
            );
    }

    public static function canEdit(Model $record): bool
    {
        return (auth()->user()?->isAdmin() ?? false)
            || (
                auth()->user()?->hasPermission(
                    'promotions.edit',
                ) ?? false
            );
    }

    public static function canDelete(Model $record): bool
    {
        return (auth()->user()?->isAdmin() ?? false)
            || (
                auth()->user()?->hasPermission(
                    'promotions.delete',
                ) ?? false
            );
    }

    public static function canDeleteAny(): bool
    {
        return (auth()->user()?->isAdmin() ?? false)
            || (
                auth()->user()?->hasPermission(
                    'promotions.delete',
                ) ?? false
            );
    }

    public static function form(Schema $schema): Schema
    {
        return PromotionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PromotionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PromotionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPromotions::route('/'),
            'create' => CreatePromotion::route('/create'),
            'view' => ViewPromotion::route('/{record}'),
            'edit' => EditPromotion::route('/{record}/edit'),
        ];
    }
}