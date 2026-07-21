<?php

declare(strict_types=1);

namespace App\Filament\Resources\AiSettings\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AiSettingInfolist
{
    public static function configure(
        Schema $schema,
    ): Schema {
        return $schema
            ->components(
                [
                    Section::make(
                        'Karwan AI Runtime Settings',
                    )
                        ->columns(
                            3,
                        )
                        ->schema(
                            [
                                TextEntry::make(
                                    'provider',
                                )
                                    ->label(
                                        'Provider',
                                    )
                                    ->badge(),

                                TextEntry::make(
                                    'model',
                                )
                                    ->label(
                                        'Model',
                                    )
                                    ->placeholder(
                                        'Local provider',
                                    ),

                                IconEntry::make(
                                    'external_enabled',
                                )
                                    ->label(
                                        'External AI',
                                    )
                                    ->boolean(),

                                IconEntry::make(
                                    'fallback_enabled',
                                )
                                    ->label(
                                        'Safe Fallback',
                                    )
                                    ->boolean(),

                                TextEntry::make(
                                    'timeout_seconds',
                                )
                                    ->label(
                                        'Timeout',
                                    )
                                    ->suffix(
                                        ' seconds',
                                    ),

                                TextEntry::make(
                                    'max_output_characters',
                                )
                                    ->label(
                                        'Output Limit',
                                    )
                                    ->numeric()
                                    ->suffix(
                                        ' characters',
                                    ),

                                TextEntry::make(
                                    'updatedBy.name',
                                )
                                    ->label(
                                        'Last Updated By',
                                    )
                                    ->placeholder(
                                        'System',
                                    ),

                                TextEntry::make(
                                    'updated_at',
                                )
                                    ->label(
                                        'Last Updated',
                                    )
                                    ->dateTime(
                                        'd M Y h:i A',
                                    ),
                            ],
                        ),
                ],
            );
    }
}
