<?php

declare(strict_types=1);

namespace App\Filament\Resources\AiSettings\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AiSettingsTable
{
    public static function configure(
        Table $table,
    ): Table {
        return $table
            ->columns(
                [
                    TextColumn::make(
                        'provider',
                    )
                        ->label(
                            'Provider',
                        )
                        ->badge()
                        ->sortable(),

                    IconColumn::make(
                        'external_enabled',
                    )
                        ->label(
                            'External AI',
                        )
                        ->boolean(),

                    IconColumn::make(
                        'fallback_enabled',
                    )
                        ->label(
                            'Fallback',
                        )
                        ->boolean(),

                    TextColumn::make(
                        'timeout_seconds',
                    )
                        ->label(
                            'Timeout',
                        )
                        ->suffix(
                            ' sec',
                        )
                        ->sortable(),

                    TextColumn::make(
                        'max_output_characters',
                    )
                        ->label(
                            'Output Limit',
                        )
                        ->numeric()
                        ->sortable(),

                    TextColumn::make(
                        'updatedBy.name',
                    )
                        ->label(
                            'Updated By',
                        )
                        ->placeholder(
                            'System',
                        ),

                    TextColumn::make(
                        'updated_at',
                    )
                        ->label(
                            'Updated',
                        )
                        ->dateTime(
                            'd M Y h:i A',
                        )
                        ->sortable(),
                ],
            )
            ->recordActions(
                [
                    ViewAction::make(),
                    EditAction::make(),
                ],
            );
    }
}
