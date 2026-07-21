<?php

declare(strict_types=1);

namespace App\Filament\Resources\AiSettings\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AiSettingForm
{
    public static function configure(
        Schema $schema,
    ): Schema {
        return $schema
            ->components(
                [
                    Section::make(
                        'Provider Safety',
                    )
                        ->description(
                            'Phase 8A3B mein external AI provider locked hai. Karwan AI local knowledge base aur rules engine use karega.',
                        )
                        ->columns(
                            2,
                        )
                        ->schema(
                            [
                                Hidden::make(
                                    'key',
                                )
                                    ->default(
                                        'default',
                                    ),

                                Select::make(
                                    'provider',
                                )
                                    ->label(
                                        'Active Provider',
                                    )
                                    ->options(
                                        [
                                            'local' =>
                                                'Local Knowledge + Rules',
                                        ],
                                    )
                                    ->default(
                                        'local',
                                    )
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(),

                                TextInput::make(
                                    'model',
                                )
                                    ->label(
                                        'External Model',
                                    )
                                    ->placeholder(
                                        'Locked until external provider implementation',
                                    )
                                    ->disabled()
                                    ->dehydrated(
                                        false,
                                    ),

                                Toggle::make(
                                    'external_enabled',
                                )
                                    ->label(
                                        'External AI Enabled',
                                    )
                                    ->helperText(
                                        'Security lock: external API requests Phase 8A3B mein disabled hain.',
                                    )
                                    ->disabled()
                                    ->dehydrated(
                                        false,
                                    ),
                            ],
                        ),

                    Section::make(
                        'Runtime Controls',
                    )
                        ->columns(
                            3,
                        )
                        ->schema(
                            [
                                Toggle::make(
                                    'fallback_enabled',
                                )
                                    ->label(
                                        'Safe Fallback Enabled',
                                    )
                                    ->default(
                                        true,
                                    )
                                    ->required(),

                                TextInput::make(
                                    'timeout_seconds',
                                )
                                    ->label(
                                        'Timeout Seconds',
                                    )
                                    ->numeric()
                                    ->minValue(
                                        1,
                                    )
                                    ->maxValue(
                                        60,
                                    )
                                    ->default(
                                        10,
                                    )
                                    ->required(),

                                TextInput::make(
                                    'max_output_characters',
                                )
                                    ->label(
                                        'Maximum Output Characters',
                                    )
                                    ->numeric()
                                    ->minValue(
                                        100,
                                    )
                                    ->maxValue(
                                        12000,
                                    )
                                    ->default(
                                        4000,
                                    )
                                    ->required(),
                            ],
                        ),
                ],
            );
    }
}
