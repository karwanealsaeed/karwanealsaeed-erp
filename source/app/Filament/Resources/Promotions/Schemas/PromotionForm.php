<?php

namespace App\Filament\Resources\Promotions\Schemas;

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

class PromotionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Promotion Information')
                    ->description(
                        'Offer ki English aur Urdu information.',
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

                        TextInput::make('subtitle')
                            ->label('Subtitle — English')
                            ->maxLength(255),

                        TextInput::make('subtitle_ur')
                            ->label('Subtitle — Urdu')
                            ->maxLength(255)
                            ->extraInputAttributes([
                                'dir' => 'rtl',
                            ]),

                        Select::make('promotion_type')
                            ->label('Promotion Type')
                            ->options([
                                'Umrah Package' => 'Umrah Package',
                                'Air Ticketing' => 'Air Ticketing',
                                'Visit Visa' => 'Visit Visa',
                                'Hotel Booking' => 'Hotel Booking',
                                'Pakistan Tours' => 'Pakistan Tours',
                                'Travel Insurance' => 'Travel Insurance',
                                'Seasonal Offer' => 'Seasonal Offer',
                                'General Offer' => 'General Offer',
                            ])
                            ->searchable()
                            ->native(false),

                        TextInput::make('promo_code')
                            ->label('Promo Code')
                            ->placeholder('UMRAH2026')
                            ->maxLength(100),

                        Textarea::make('description')
                            ->label('Description — English')
                            ->rows(6)
                            ->maxLength(5000)
                            ->columnSpanFull(),

                        Textarea::make('description_ur')
                            ->label('Description — Urdu')
                            ->rows(6)
                            ->maxLength(5000)
                            ->extraInputAttributes([
                                'dir' => 'rtl',
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Discount and Pricing')
                    ->schema([
                        Select::make('discount_type')
                            ->label('Discount Type')
                            ->options([
                                'Percentage' => 'Percentage',
                                'Fixed Amount' => 'Fixed Amount',
                                'Special Price' => 'Special Price',
                            ])
                            ->live()
                            ->native(false),

                        TextInput::make('discount_value')
                            ->label('Discount Value')
                            ->numeric()
                            ->minValue(0)
                            ->visible(
                                fn (Get $get): bool => in_array(
                                    $get('discount_type'),
                                    [
                                        'Percentage',
                                        'Fixed Amount',
                                    ],
                                    true,
                                ),
                            ),

                        TextInput::make('currency')
                            ->label('Currency')
                            ->placeholder('PKR')
                            ->maxLength(3),

                        TextInput::make('original_price')
                            ->label('Original Price')
                            ->numeric()
                            ->minValue(0),

                        TextInput::make('offer_price')
                            ->label('Offer Price')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->columns(2),

                Section::make('Creative and Call to Action')
                    ->schema([
                        FileUpload::make(
                            'featured_image_path',
                        )
                            ->label('Promotion Image')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory(
                                'promotions/images',
                            )
                            ->visibility('public')
                            ->maxSize(8192)
                            ->helperText(
                                'Recommended landscape image. Maximum 8 MB.',
                            )
                            ->columnSpanFull(),

                        TextInput::make('badge_text')
                            ->label('Badge Text — English')
                            ->placeholder(
                                'Limited Time Offer',
                            )
                            ->maxLength(255),

                        TextInput::make('badge_text_ur')
                            ->label('Badge Text — Urdu')
                            ->maxLength(255)
                            ->extraInputAttributes([
                                'dir' => 'rtl',
                            ]),

                        TextInput::make('cta_text')
                            ->label('Button Text — English')
                            ->placeholder('Book Now')
                            ->maxLength(255),

                        TextInput::make('cta_text_ur')
                            ->label('Button Text — Urdu')
                            ->maxLength(255)
                            ->extraInputAttributes([
                                'dir' => 'rtl',
                            ]),

                        TextInput::make('target_url')
                            ->label('Target URL')
                            ->placeholder(
                                '/contact or https://example.com',
                            )
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Terms and Validity')
                    ->schema([
                        Textarea::make('terms')
                            ->label('Terms — English')
                            ->rows(5)
                            ->maxLength(5000)
                            ->columnSpanFull(),

                        Textarea::make('terms_ur')
                            ->label('Terms — Urdu')
                            ->rows(5)
                            ->maxLength(5000)
                            ->extraInputAttributes([
                                'dir' => 'rtl',
                            ])
                            ->columnSpanFull(),

                        DateTimePicker::make('starts_at')
                            ->label('Offer Starts At')
                            ->native(false),

                        DateTimePicker::make('ends_at')
                            ->label('Offer Ends At')
                            ->native(false),
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
                                'Blank chhorne par publish karte waqt automatically set ho jayega.',
                            )
                            ->native(false)
                            ->visible(
                                fn (Get $get): bool =>
                                    $get('status') === 'Published',
                            ),

                        Toggle::make('is_featured')
                            ->label('Featured Promotion')
                            ->default(false),

                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->integer()
                            ->minValue(0)
                            ->default(0),
                    ])
                    ->columns(2),
            ]);
    }
}