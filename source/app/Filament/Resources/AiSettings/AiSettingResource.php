<?php

declare(strict_types=1);

namespace App\Filament\Resources\AiSettings;

use App\Filament\Resources\AiSettings\Pages\EditAiSetting;
use App\Filament\Resources\AiSettings\Pages\ListAiSettings;
use App\Filament\Resources\AiSettings\Pages\ViewAiSetting;
use App\Filament\Resources\AiSettings\Schemas\AiSettingForm;
use App\Filament\Resources\AiSettings\Schemas\AiSettingInfolist;
use App\Filament\Resources\AiSettings\Tables\AiSettingsTable;
use App\Models\AiSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class AiSettingResource extends Resource
{
    protected static ?string $model = AiSetting::class;

    protected static string|BackedEnum|null $navigationIcon =
        'heroicon-o-adjustments-horizontal';

    protected static string|UnitEnum|null $navigationGroup =
        'Settings';

    protected static ?int $navigationSort =
        91;

    protected static ?string $navigationLabel =
        'Karwan AI Settings';

    protected static ?string $modelLabel =
        'Karwan AI Setting';

    protected static ?string $pluralModelLabel =
        'Karwan AI Settings';

    protected static ?string $recordTitleAttribute =
        'key';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasPermission(
            'ai_settings_view',
        ) ?? false;
    }

    public static function canViewAny(): bool
    {
        return self::canAccess();
    }

    public static function canView($record): bool
    {
        return self::canAccess();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasPermission(
            'ai_settings_edit',
        ) ?? false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return AiSettingForm::configure(
            $schema,
        );
    }

    public static function infolist(Schema $schema): Schema
    {
        return AiSettingInfolist::configure(
            $schema,
        );
    }

    public static function table(Table $table): Table
    {
        return AiSettingsTable::configure(
            $table,
        );
    }

    public static function getPages(): array
    {
        return [
            'index' =>
                ListAiSettings::route(
                    '/',
                ),

            'view' =>
                ViewAiSetting::route(
                    '/{record}',
                ),

            'edit' =>
                EditAiSetting::route(
                    '/{record}/edit',
                ),
        ];
    }
}
