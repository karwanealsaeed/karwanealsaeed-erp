<?php

namespace App\Filament\Resources\Faqs\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class FaqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('FAQ Information')
                    ->description(
                        'English aur Urdu sawal, jawab aur category.',
                    )
                    ->schema([
                        TextInput::make('question')
                            ->label('Question — English')
                            ->required()
                            ->maxLength(500)
                            ->columnSpanFull(),

                        TextInput::make('question_ur')
                            ->label('Question — Urdu')
                            ->maxLength(500)
                            ->extraInputAttributes([
                                'dir' => 'rtl',
                            ])
                            ->columnSpanFull(),

                        TextInput::make('category')
                            ->label('Category — English')
                            ->placeholder(
                                'Umrah, Visa, Tickets, Booking...',
                            )
                            ->maxLength(255),

                        TextInput::make('category_ur')
                            ->label('Category — Urdu')
                            ->maxLength(255)
                            ->extraInputAttributes([
                                'dir' => 'rtl',
                            ]),

                        Textarea::make('answer')
                            ->label('Answer — English')
                            ->required()
                            ->rows(7)
                            ->maxLength(5000)
                            ->columnSpanFull(),

                        Textarea::make('answer_ur')
                            ->label('Answer — Urdu')
                            ->rows(7)
                            ->maxLength(5000)
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
                            ->label('Featured FAQ')
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