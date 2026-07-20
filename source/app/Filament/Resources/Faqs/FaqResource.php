<?php

namespace App\Filament\Resources\Faqs;

use App\Filament\Resources\Faqs\Pages\CreateFaq;
use App\Filament\Resources\Faqs\Pages\EditFaq;
use App\Filament\Resources\Faqs\Pages\ListFaqs;
use App\Filament\Resources\Faqs\Pages\ViewFaq;
use App\Filament\Resources\Faqs\Schemas\FaqForm;
use App\Filament\Resources\Faqs\Schemas\FaqInfolist;
use App\Filament\Resources\Faqs\Tables\FaqsTable;
use App\Models\Faq;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;

    protected static string|\BackedEnum|null $navigationIcon =
        'heroicon-o-question-mark-circle';

    protected static string|\UnitEnum|null $navigationGroup =
        'Website Content';

    protected static ?int $navigationSort = 40;

    protected static ?string $navigationLabel =
        'FAQs';

    protected static ?string $modelLabel =
        'FAQ';

    protected static ?string $pluralModelLabel =
        'FAQs';

    protected static ?string $recordTitleAttribute =
        'question';

    public static function canAccess(): bool
    {
        return (auth()->user()?->isAdmin() ?? false)
            || (
                auth()->user()?->hasPermission(
                    'faqs.view',
                ) ?? false
            );
    }

    public static function canCreate(): bool
    {
        return (auth()->user()?->isAdmin() ?? false)
            || (
                auth()->user()?->hasPermission(
                    'faqs.create',
                ) ?? false
            );
    }

    public static function canEdit(Model $record): bool
    {
        return (auth()->user()?->isAdmin() ?? false)
            || (
                auth()->user()?->hasPermission(
                    'faqs.edit',
                ) ?? false
            );
    }

    public static function canDelete(Model $record): bool
    {
        return (auth()->user()?->isAdmin() ?? false)
            || (
                auth()->user()?->hasPermission(
                    'faqs.delete',
                ) ?? false
            );
    }

    public static function canDeleteAny(): bool
    {
        return (auth()->user()?->isAdmin() ?? false)
            || (
                auth()->user()?->hasPermission(
                    'faqs.delete',
                ) ?? false
            );
    }

    public static function form(Schema $schema): Schema
    {
        return FaqForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FaqInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FaqsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFaqs::route('/'),
            'create' => CreateFaq::route('/create'),
            'view' => ViewFaq::route('/{record}'),
            'edit' => EditFaq::route('/{record}/edit'),
        ];
    }
}