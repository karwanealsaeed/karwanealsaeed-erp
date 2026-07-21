<?php

declare(strict_types=1);

namespace App\Filament\Resources\AiKnowledgeBases;

use App\Filament\Resources\AiKnowledgeBases\Pages\CreateAiKnowledgeBase;
use App\Filament\Resources\AiKnowledgeBases\Pages\EditAiKnowledgeBase;
use App\Filament\Resources\AiKnowledgeBases\Pages\ListAiKnowledgeBases;
use App\Filament\Resources\AiKnowledgeBases\Pages\ViewAiKnowledgeBase;
use App\Filament\Resources\AiKnowledgeBases\Schemas\AiKnowledgeBaseForm;
use App\Filament\Resources\AiKnowledgeBases\Schemas\AiKnowledgeBaseInfolist;
use App\Filament\Resources\AiKnowledgeBases\Tables\AiKnowledgeBasesTable;
use App\Models\AiKnowledgeBase;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class AiKnowledgeBaseResource extends Resource
{
    protected static ?string $model =
        AiKnowledgeBase::class;

    protected static string|BackedEnum|null $navigationIcon =
        'heroicon-o-sparkles';

    protected static string|UnitEnum|null $navigationGroup =
        'Settings';

    protected static ?string $navigationLabel =
        'AI Knowledge Base';

    protected static ?string $modelLabel =
        'AI Knowledge';

    protected static ?string $pluralModelLabel =
        'AI Knowledge Base';

    protected static ?string $recordTitleAttribute =
        'title';

    protected static ?int $navigationSort =
        90;

    public static function canAccess(): bool
    {
        return auth()
            ->user()
            ?->hasPermission(
                'ai_knowledge_bases.view',
            )
            ?? false;
    }

    public static function canViewAny(): bool
    {
        return self::canAccess();
    }

    public static function canView(
        Model $record,
    ): bool {
        return self::canAccess();
    }

    public static function canCreate(): bool
    {
        return auth()
            ->user()
            ?->hasPermission(
                'ai_knowledge_bases.create',
            )
            ?? false;
    }

    public static function canEdit(
        Model $record,
    ): bool {
        return auth()
            ->user()
            ?->hasPermission(
                'ai_knowledge_bases.edit',
            )
            ?? false;
    }

    public static function canDelete(
        Model $record,
    ): bool {
        return auth()
            ->user()
            ?->hasPermission(
                'ai_knowledge_bases.delete',
            )
            ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()
            ->user()
            ?->hasPermission(
                'ai_knowledge_bases.delete',
            )
            ?? false;
    }

    public static function form(
        Schema $schema,
    ): Schema {
        return AiKnowledgeBaseForm::configure(
            $schema,
        );
    }

    public static function infolist(
        Schema $schema,
    ): Schema {
        return AiKnowledgeBaseInfolist::configure(
            $schema,
        );
    }

    public static function table(
        Table $table,
    ): Table {
        return AiKnowledgeBasesTable::configure(
            $table,
        );
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' =>
                ListAiKnowledgeBases::route(
                    '/',
                ),

            'create' =>
                CreateAiKnowledgeBase::route(
                    '/create',
                ),

            'view' =>
                ViewAiKnowledgeBase::route(
                    '/{record}',
                ),

            'edit' =>
                EditAiKnowledgeBase::route(
                    '/{record}/edit',
                ),
        ];
    }
}
