<?php

namespace App\Filament\Resources\BlogPosts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BlogPostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Article Information')
                    ->description(
                        'Blog post ki basic English aur Urdu information.',
                    )
                    ->schema([
                        TextInput::make('title')
                            ->label('Title — English')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                fn (
                                    Set $set,
                                    ?string $state,
                                ) => $set(
                                    'slug',
                                    Str::slug((string) $state),
                                ),
                            )
                            ->columnSpanFull(),

                        TextInput::make('title_ur')
                            ->label('Title — Urdu')
                            ->maxLength(255)
                            ->extraInputAttributes([
                                'dir' => 'rtl',
                            ])
                            ->columnSpanFull(),

                        TextInput::make('slug')
                            ->helperText(
                                'Blank chhorne par title se automatically generate ho jayega.',
                            )
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->columnSpanFull(),

                        TextInput::make('category')
                            ->label('Category — English')
                            ->placeholder(
                                'Travel Guide, Umrah, Visa News...',
                            )
                            ->maxLength(255),

                        TextInput::make('category_ur')
                            ->label('Category — Urdu')
                            ->maxLength(255)
                            ->extraInputAttributes([
                                'dir' => 'rtl',
                            ]),

                        TextInput::make('author_name')
                            ->label('Author Name')
                            ->placeholder(
                                'Karwan E Al Saeed Team',
                            )
                            ->maxLength(255),

                        TagsInput::make('tags')
                            ->placeholder(
                                'Umrah, Travel, Visa...',
                            )
                            ->columnSpanFull(),

                        Textarea::make('excerpt')
                            ->label('Short Excerpt — English')
                            ->rows(4)
                            ->maxLength(1000)
                            ->columnSpanFull(),

                        Textarea::make('excerpt_ur')
                            ->label('Short Excerpt — Urdu')
                            ->rows(4)
                            ->maxLength(1000)
                            ->extraInputAttributes([
                                'dir' => 'rtl',
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Article Content')
                    ->schema([
                        RichEditor::make('content')
                            ->label('Content — English')
                            ->required()
                            ->columnSpanFull(),

                        RichEditor::make('content_ur')
                            ->label('Content — Urdu')
                            ->extraAttributes([
                                'dir' => 'rtl',
                            ])
                            ->columnSpanFull(),
                    ]),

                Section::make('Featured Image')
                    ->schema([
                        FileUpload::make(
                            'featured_image_path',
                        )
                            ->label('Featured Image')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory(
                                'blog/featured-images',
                            )
                            ->visibility('public')
                            ->maxSize(8192)
                            ->helperText(
                                'Recommended landscape image. Maximum 8 MB.',
                            )
                            ->columnSpanFull(),

                        TextInput::make('image_alt_text')
                            ->label(
                                'Image Alt Text — English',
                            )
                            ->maxLength(255),

                        TextInput::make(
                            'image_alt_text_ur',
                        )
                            ->label(
                                'Image Alt Text — Urdu',
                            )
                            ->maxLength(255)
                            ->extraInputAttributes([
                                'dir' => 'rtl',
                            ]),
                    ])
                    ->columns(2),

                Section::make('SEO Settings')
                    ->collapsed()
                    ->schema([
                        TextInput::make('seo_title')
                            ->label('SEO Title — English')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('seo_title_ur')
                            ->label('SEO Title — Urdu')
                            ->maxLength(255)
                            ->extraInputAttributes([
                                'dir' => 'rtl',
                            ])
                            ->columnSpanFull(),

                        Textarea::make('seo_description')
                            ->label(
                                'SEO Description — English',
                            )
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),

                        Textarea::make(
                            'seo_description_ur',
                        )
                            ->label(
                                'SEO Description — Urdu',
                            )
                            ->rows(3)
                            ->maxLength(500)
                            ->extraInputAttributes([
                                'dir' => 'rtl',
                            ])
                            ->columnSpanFull(),
                    ]),

                Section::make('Publication')
                    ->schema([
                        Select::make('status')
                            ->options([
                                'Draft' => 'Draft',
                                'Published' => 'Published',
                                'Archived' => 'Archived',
                            ])
                            ->default('Draft')
                            ->required()
                            ->live()
                            ->native(false),

                        DateTimePicker::make('published_at')
                            ->label('Publish Date and Time')
                            ->helperText(
                                'Blank chhorne par publish karte waqt automatically set ho jayega.',
                            )
                            ->native(false)
                            ->visible(
                                fn (Get $get): bool =>
                                    $get('status') === 'Published',
                            ),

                        Toggle::make('is_featured')
                            ->label('Featured Article')
                            ->default(false),

                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->integer()
                            ->minValue(0)
                            ->default(0),

                        TextInput::make('view_count')
                            ->label('View Count')
                            ->integer()
                            ->minValue(0)
                            ->default(0)
                            ->disabled()
                            ->dehydrated(),
                    ])
                    ->columns(2),
            ]);
    }
}