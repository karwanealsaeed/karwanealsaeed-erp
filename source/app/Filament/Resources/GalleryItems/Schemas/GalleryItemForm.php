<?php

namespace App\Filament\Resources\GalleryItems\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class GalleryItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->description(
                        'Gallery item ki English aur Urdu information.',
                    )
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->live(debounce: 500)
                            ->afterStateUpdated(function (
                                ?string $state,
                                Get $get,
                                Set $set,
                            ): void {
                                $title = trim((string) $state);

                                if (blank($get('slug'))) {
                                    $set('slug', Str::slug($title));
                                }

                                if (blank($get('alt_text'))) {
                                    $set('alt_text', $title);
                                }
                            })
                            ->maxLength(255),

                        TextInput::make('title_ur')
                            ->label('Title — Urdu')
                            ->live(debounce: 500)
                            ->afterStateUpdated(function (
                                ?string $state,
                                Get $get,
                                Set $set,
                            ): void {
                                if (blank($get('alt_text_ur'))) {
                                    $set(
                                        'alt_text_ur',
                                        trim((string) $state),
                                    );
                                }
                            })
                            ->maxLength(255)
                            ->extraInputAttributes([
                                'dir' => 'rtl',
                            ]),

                        TextInput::make('slug')
                            ->helperText(
                                'Title se automatically generate hoga.',
                            )
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('category')
                            ->placeholder(
                                'Umrah, Pakistan Tours, Events...',
                            )
                            ->maxLength(255),

                        TextInput::make('category_ur')
                            ->label('Category — Urdu')
                            ->maxLength(255)
                            ->extraInputAttributes([
                                'dir' => 'rtl',
                            ]),

                        TextInput::make('album')
                            ->placeholder('Album name')
                            ->maxLength(255),

                        TextInput::make('album_ur')
                            ->label('Album — Urdu')
                            ->maxLength(255)
                            ->extraInputAttributes([
                                'dir' => 'rtl',
                            ]),
                    ])
                    ->columns(2),

                Section::make('Media')
                    ->description(
                        'Image upload karein ya video URL add karein.',
                    )
                    ->schema([
                        Select::make('media_type')
                            ->options([
                                'Image' => 'Image',
                                'Video' => 'Video',
                            ])
                            ->default('Image')
                            ->required()
                            ->live()
                            ->native(false),

                        FileUpload::make('image_path')
                            ->label('Gallery Image')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('gallery/images')
                            ->visibility('public')
                            ->maxSize(8192)
                            ->required(
                                fn (Get $get): bool =>
                                    $get('media_type') === 'Image',
                            )
                            ->visible(
                                fn (Get $get): bool =>
                                    $get('media_type') === 'Image',
                            ),

                        FileUpload::make('thumbnail_path')
                            ->label('Video Thumbnail')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('gallery/thumbnails')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->visible(
                                fn (Get $get): bool =>
                                    $get('media_type') === 'Video',
                            ),

                        TextInput::make('video_url')
                            ->label('Video URL')
                            ->url()
                            ->placeholder(
                                'https://youtube.com/...',
                            )
                            ->required(
                                fn (Get $get): bool =>
                                    $get('media_type') === 'Video',
                            )
                            ->visible(
                                fn (Get $get): bool =>
                                    $get('media_type') === 'Video',
                            )
                            ->maxLength(500),

                        TextInput::make('alt_text')
                            ->label('Image Alt Text')
                            ->helperText(
                                'SEO aur accessibility ke liye.',
                            )
                            ->maxLength(255),

                        TextInput::make('alt_text_ur')
                            ->label('Alt Text — Urdu')
                            ->maxLength(255)
                            ->extraInputAttributes([
                                'dir' => 'rtl',
                            ]),
                    ])
                    ->columns(2),

                Section::make('Caption and Details')
                    ->schema([
                        Textarea::make('caption')
                            ->rows(4)
                            ->maxLength(2000)
                            ->columnSpanFull(),

                        Textarea::make('caption_ur')
                            ->label('Caption — Urdu')
                            ->rows(4)
                            ->maxLength(2000)
                            ->extraInputAttributes([
                                'dir' => 'rtl',
                            ])
                            ->columnSpanFull(),

                        TextInput::make('location')
                            ->maxLength(255),

                        TextInput::make('location_ur')
                            ->label('Location — Urdu')
                            ->maxLength(255)
                            ->extraInputAttributes([
                                'dir' => 'rtl',
                            ]),

                        DatePicker::make('event_date')
                            ->label('Event Date')
                            ->native(false),

                        TextInput::make('photographer')
                            ->maxLength(255),
                    ])
                    ->columns(2),

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
                                'Blank chhorne par publish hote waqt automatically set hoga.',
                            )
                            ->native(false)
                            ->visible(
                                fn (Get $get): bool =>
                                    $get('status') === 'Published',
                            ),

                        Toggle::make('is_featured')
                            ->label('Featured Gallery Item')
                            ->default(false),

                        TextInput::make('sort_order')
                            ->integer()
                            ->minValue(0)
                            ->default(0),
                    ])
                    ->columns(2),
            ]);
    }
}