<?php

namespace App\Filament\Resources\PakistanTours\Schemas;

use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
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

class PakistanTourForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->description(
                        'Main English and Urdu tour information.',
                    )
                    ->schema([
                        TextInput::make('tour_no')
                            ->label('Tour Number')
                            ->placeholder('Auto-generated')
                            ->disabled()
                            ->dehydrated(),

                        TextInput::make('slug')
                            ->helperText(
                                'Leave blank to generate automatically.',
                            )
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('title')
    ->required()
    ->live(debounce: 500)
    ->afterStateUpdated(function (
        ?string $state,
        Set $set,
    ): void {
        $title = trim((string) $state);

        $set('slug', Str::slug($title));
        $set(
            'meta_title',
            Str::limit($title, 60, ''),
        );
    })
    ->maxLength(255),

                        TextInput::make('title_ur')
    ->label('Title — Urdu')
    ->live(debounce: 500)
    ->afterStateUpdated(function (
        ?string $state,
        Set $set,
    ): void {
        $set(
            'meta_title_ur',
            Str::limit(trim((string) $state), 60, ''),
        );
    })
    ->maxLength(255)
    ->extraInputAttributes([
        'dir' => 'rtl',
    ]),

                        TextInput::make('subtitle')
                            ->maxLength(255),

                        TextInput::make('subtitle_ur')
                            ->label('Subtitle — Urdu')
                            ->maxLength(255)
                            ->extraInputAttributes([
                                'dir' => 'rtl',
                            ]),

                        TextInput::make('destination')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('province')
                            ->maxLength(255),

                        TextInput::make('departure_city')
                            ->label('Departure City')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('Schedule and Capacity')
                    ->schema([
                        DatePicker::make('start_date')
    ->native(false)
    ->live()
    ->afterStateUpdated(function (
        mixed $state,
        Get $get,
        Set $set,
    ): void {
        if (blank($state) || blank($get('end_date'))) {
            return;
        }

        $startDate = Carbon::parse($state);
        $endDate = Carbon::parse($get('end_date'));

        if ($endDate->lt($startDate)) {
            return;
        }

        $nights = $startDate->diffInDays($endDate);

        $set('duration_nights', $nights);
        $set('duration_days', $nights + 1);
    }),

DatePicker::make('end_date')
    ->native(false)
    ->minDate(fn (Get $get) => $get('start_date'))
    ->live()
    ->afterStateUpdated(function (
        mixed $state,
        Get $get,
        Set $set,
    ): void {
        if (blank($state) || blank($get('start_date'))) {
            return;
        }

        $startDate = Carbon::parse($get('start_date'));
        $endDate = Carbon::parse($state);

        if ($endDate->lt($startDate)) {
            return;
        }

        $nights = $startDate->diffInDays($endDate);

        $set('duration_nights', $nights);
        $set('duration_days', $nights + 1);
    }),

                        TextInput::make('duration_days')
                            ->label('Duration — Days')
                            ->integer()
                            ->minValue(1),

                        TextInput::make('duration_nights')
                            ->label('Duration — Nights')
                            ->integer()
                            ->minValue(0),

                        TextInput::make('group_size')
                            ->integer()
                            ->minValue(0),

                        TextInput::make('seats_available')
    ->integer()
    ->minValue(0)
    ->live(onBlur: true)
    ->afterStateUpdated(function (
        mixed $state,
        Get $get,
        Set $set,
    ): void {
        if (blank($get('seats_left'))) {
            $set('seats_left', (int) $state);
        }
    }),

                        TextInput::make('seats_left')
                            ->integer()
                            ->minValue(0),
                    ])
                    ->columns(3),

                Section::make('Pricing and Publication')
                    ->schema([
                        TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->prefix('PKR'),

                        TextInput::make('price_note')
                            ->maxLength(255),

                        TextInput::make('price_note_ur')
                            ->label('Price Note — Urdu')
                            ->maxLength(255)
                            ->extraInputAttributes([
                                'dir' => 'rtl',
                            ]),

                        Select::make('status')
                            ->required()
                            ->options([
                                'Draft' => 'Draft',
                                'Published' => 'Published',
                                'Archived' => 'Archived',
                            ])
                            ->default('Draft')
                            ->native(false),

                        Toggle::make('is_featured')
                            ->label('Featured Tour')
                            ->default(false),

                        TextInput::make('sort_order')
                            ->integer()
                            ->minValue(0)
                            ->default(0),
                    ])
                    ->columns(3),

                Section::make('Images')
                    ->schema([
                        FileUpload::make('cover_image')
                            ->label('Cover Image')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('pakistan-tours/covers')
                            ->visibility('public')
                            ->maxSize(5120),

                        FileUpload::make('gallery_images')
                            ->label('Gallery Images')
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->disk('public')
                            ->directory('pakistan-tours/gallery')
                            ->visibility('public')
                            ->maxFiles(12)
                            ->maxSize(5120),
                    ])
                    ->columns(2),

                Section::make('Descriptions')
                    ->schema([
                        Textarea::make('short_description')
    ->rows(4)
    ->live(debounce: 700)
    ->afterStateUpdated(function (
        ?string $state,
        Set $set,
    ): void {
        $set(
            'meta_description',
            Str::limit(
                trim((string) $state),
                160,
                '',
            ),
        );
    })
    ->maxLength(1000)
    ->columnSpanFull(),

                        Textarea::make('short_description_ur')
    ->label('Short Description — Urdu')
    ->rows(4)
    ->live(debounce: 700)
    ->afterStateUpdated(function (
        ?string $state,
        Set $set,
    ): void {
        $set(
            'meta_description_ur',
            Str::limit(
                trim((string) $state),
                160,
                '',
            ),
        );
    })
    ->maxLength(1000)
    ->extraInputAttributes([
        'dir' => 'rtl',
    ])
    ->columnSpanFull(),

                        Textarea::make('description')
                            ->rows(10)
                            ->columnSpanFull(),

                        Textarea::make('description_ur')
                            ->label('Full Description — Urdu')
                            ->rows(10)
                            ->extraInputAttributes([
                                'dir' => 'rtl',
                            ])
                            ->columnSpanFull(),
                    ]),

                Section::make('Tour Details')
                    ->description(
                        'Press Enter after each separate item.',
                    )
                    ->schema([
                        TagsInput::make('highlights')
                            ->splitKeys(['Tab', 'Enter']),

                        TagsInput::make('highlights_ur')
                            ->label('Highlights — Urdu')
                            ->splitKeys(['Tab', 'Enter'])
                            ->extraInputAttributes([
                                'dir' => 'rtl',
                            ]),

                        TagsInput::make('inclusions')
                            ->splitKeys(['Tab', 'Enter']),

                        TagsInput::make('inclusions_ur')
                            ->label('Inclusions — Urdu')
                            ->splitKeys(['Tab', 'Enter'])
                            ->extraInputAttributes([
                                'dir' => 'rtl',
                            ]),

                        TagsInput::make('exclusions')
                            ->splitKeys(['Tab', 'Enter']),

                        TagsInput::make('exclusions_ur')
                            ->label('Exclusions — Urdu')
                            ->splitKeys(['Tab', 'Enter'])
                            ->extraInputAttributes([
                                'dir' => 'rtl',
                            ]),
                    ])
                    ->columns(2),

                Section::make('Search Engine Optimization')
                    ->collapsed()
                    ->schema([
                        TextInput::make('meta_title')
                            ->maxLength(60),

                        TextInput::make('meta_title_ur')
                            ->label('Meta Title — Urdu')
                            ->maxLength(60)
                            ->extraInputAttributes([
                                'dir' => 'rtl',
                            ]),

                        Textarea::make('meta_description')
                            ->rows(3)
                            ->maxLength(160),

                        Textarea::make('meta_description_ur')
                            ->label('Meta Description — Urdu')
                            ->rows(3)
                            ->maxLength(160)
                            ->extraInputAttributes([
                                'dir' => 'rtl',
                            ]),
                    ])
                    ->columns(2),
            ]);
    }
}
