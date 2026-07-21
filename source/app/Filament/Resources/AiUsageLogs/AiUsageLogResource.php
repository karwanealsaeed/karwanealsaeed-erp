<?php

declare(strict_types=1);

namespace App\Filament\Resources\AiUsageLogs;

use App\Filament\Resources\AiUsageLogs\Pages\ListAiUsageLogs;
use App\Filament\Resources\AiUsageLogs\Pages\ViewAiUsageLog;
use App\Filament\Resources\AiUsageLogs\Schemas\AiUsageLogInfolist;
use App\Filament\Resources\AiUsageLogs\Tables\AiUsageLogsTable;
use App\Models\AiUsageLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class AiUsageLogResource extends Resource
{
    protected static ?string $model = AiUsageLog::class;

    protected static string|BackedEnum|null $navigationIcon =
        'heroicon-o-chart-bar-square';

    protected static string|UnitEnum|null $navigationGroup =
        'Settings';

    protected static ?int $navigationSort =
        92;

    protected static ?string $navigationLabel =
        'Karwan AI Usage';

    protected static ?string $modelLabel =
        'AI Usage Log';

    protected static ?string $pluralModelLabel =
        'Karwan AI Usage Logs';

    protected static ?string $recordTitleAttribute =
        'request_uuid';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasPermission(
            'ai_usage_logs_view',
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
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return AiUsageLogInfolist::configure(
            $schema,
        );
    }

    public static function table(Table $table): Table
    {
        return AiUsageLogsTable::configure(
            $table,
        );
    }

    public static function getPages(): array
    {
        return [
            'index' =>
                ListAiUsageLogs::route(
                    '/',
                ),

            'view' =>
                ViewAiUsageLog::route(
                    '/{record}',
                ),
        ];
    }
}
