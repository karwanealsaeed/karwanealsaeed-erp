<?php

namespace App\Filament\Resources\Testimonials;

use App\Filament\Resources\Testimonials\Pages\CreateTestimonial;
use App\Filament\Resources\Testimonials\Pages\EditTestimonial;
use App\Filament\Resources\Testimonials\Pages\ListTestimonials;
use App\Filament\Resources\Testimonials\Pages\ViewTestimonial;
use App\Filament\Resources\Testimonials\Schemas\TestimonialForm;
use App\Filament\Resources\Testimonials\Schemas\TestimonialInfolist;
use App\Filament\Resources\Testimonials\Tables\TestimonialsTable;
use App\Models\Testimonial;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static string|\BackedEnum|null $navigationIcon =
        'heroicon-o-chat-bubble-left-right';

    protected static string|\UnitEnum|null $navigationGroup =
        'Website Content';

    protected static ?int $navigationSort = 30;

    protected static ?string $navigationLabel =
        'Testimonials';

    protected static ?string $modelLabel =
        'Testimonial';

    protected static ?string $pluralModelLabel =
        'Testimonials';

    protected static ?string $recordTitleAttribute =
        'customer_name';

    public static function canAccess(): bool
    {
        return (auth()->user()?->isAdmin() ?? false)
            || (
                auth()->user()?->hasPermission(
                    'testimonials.view',
                ) ?? false
            );
    }

    public static function canCreate(): bool
    {
        return (auth()->user()?->isAdmin() ?? false)
            || (
                auth()->user()?->hasPermission(
                    'testimonials.create',
                ) ?? false
            );
    }

    public static function canEdit(Model $record): bool
    {
        return (auth()->user()?->isAdmin() ?? false)
            || (
                auth()->user()?->hasPermission(
                    'testimonials.edit',
                ) ?? false
            );
    }

    public static function canDelete(Model $record): bool
    {
        return (auth()->user()?->isAdmin() ?? false)
            || (
                auth()->user()?->hasPermission(
                    'testimonials.delete',
                ) ?? false
            );
    }

    public static function canDeleteAny(): bool
    {
        return (auth()->user()?->isAdmin() ?? false)
            || (
                auth()->user()?->hasPermission(
                    'testimonials.delete',
                ) ?? false
            );
    }

    public static function form(Schema $schema): Schema
    {
        return TestimonialForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TestimonialInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TestimonialsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTestimonials::route('/'),
            'create' => CreateTestimonial::route('/create'),
            'view' => ViewTestimonial::route('/{record}'),
            'edit' => EditTestimonial::route('/{record}/edit'),
        ];
    }
}