<?php

namespace App\Filament\Resources\Testimonials\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class TestimonialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer Information')
                    ->description(
                        'Customer ki English aur Urdu profile information.',
                    )
                    ->schema([
                        TextInput::make('customer_name')
                            ->label('Customer Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('customer_name_ur')
                            ->label('Customer Name — Urdu')
                            ->maxLength(255)
                            ->extraInputAttributes([
                                'dir' => 'rtl',
                            ]),

                        TextInput::make('designation')
                            ->placeholder(
                                'Business Owner, Traveller...',
                            )
                            ->maxLength(255),

                        TextInput::make('designation_ur')
                            ->label('Designation — Urdu')
                            ->maxLength(255)
                            ->extraInputAttributes([
                                'dir' => 'rtl',
                            ]),

                        TextInput::make('company_name')
                            ->label('Company Name')
                            ->maxLength(255),

                        TextInput::make('country')
                            ->maxLength(255),

                        TextInput::make('location')
                            ->placeholder('Multan, Lahore, Dubai...')
                            ->maxLength(255),

                        TextInput::make('location_ur')
                            ->label('Location — Urdu')
                            ->maxLength(255)
                            ->extraInputAttributes([
                                'dir' => 'rtl',
                            ]),
                    ])
                    ->columns(2),

                Section::make('Customer Photo')
                    ->schema([
                        FileUpload::make('photo_path')
                            ->label('Profile Photo')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('testimonials/photos')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->helperText(
                                'Square customer photo recommended. Maximum 5 MB.',
                            ),
                    ]),

                Section::make('Testimonial')
                    ->description(
                        'Customer review, rating aur related service.',
                    )
                    ->schema([
                        Select::make('rating')
                            ->options([
                                1 => '★☆☆☆☆ — 1 Star',
                                2 => '★★☆☆☆ — 2 Stars',
                                3 => '★★★☆☆ — 3 Stars',
                                4 => '★★★★☆ — 4 Stars',
                                5 => '★★★★★ — 5 Stars',
                            ])
                            ->default(5)
                            ->required()
                            ->native(false),

                        Select::make('service_type')
                            ->label('Service Type')
                            ->options([
                                'Umrah Package' => 'Umrah Package',
                                'Visit Visa' => 'Visit Visa',
                                'Air Ticketing' => 'Air Ticketing',
                                'Hotel Booking' => 'Hotel Booking',
                                'Pakistan Tours' => 'Pakistan Tours',
                                'Travel Insurance' => 'Travel Insurance',
                                'Consultancy' => 'Consultancy',
                                'Other' => 'Other',
                            ])
                            ->searchable()
                            ->native(false),

                        DatePicker::make('travel_date')
                            ->label('Travel / Service Date')
                            ->native(false),

                        TextInput::make('video_url')
                            ->label('Video Testimonial URL')
                            ->url()
                            ->placeholder(
                                'https://youtube.com/...',
                            )
                            ->maxLength(500),

                        Textarea::make('testimonial')
                            ->label('Testimonial — English')
                            ->required()
                            ->rows(5)
                            ->maxLength(3000)
                            ->columnSpanFull(),

                        Textarea::make('testimonial_ur')
                            ->label('Testimonial — Urdu')
                            ->rows(5)
                            ->maxLength(3000)
                            ->extraInputAttributes([
                                'dir' => 'rtl',
                            ])
                            ->columnSpanFull(),
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
                            ->label('Featured Testimonial')
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