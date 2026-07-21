<?php

declare(strict_types=1);

namespace App\Filament\Resources\AiUsageLogs\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AiUsageLogsTable
{
    public static function configure(
        Table $table,
    ): Table {
        return $table
            ->defaultSort(
                'id',
                'desc',
            )
            ->columns(
                [
                    TextColumn::make(
                        'request_uuid',
                    )
                        ->label(
                            'Request ID',
                        )
                        ->limit(
                            12,
                        )
                        ->copyable()
                        ->searchable(),

                    TextColumn::make(
                        'customer.name',
                    )
                        ->label(
                            'Customer',
                        )
                        ->placeholder(
                            'Public visitor',
                        )
                        ->searchable(),

                    TextColumn::make(
                        'requested_provider',
                    )
                        ->label(
                            'Requested',
                        )
                        ->badge(),

                    TextColumn::make(
                        'provider',
                    )
                        ->label(
                            'Actual',
                        )
                        ->badge(),

                    TextColumn::make(
                        'source',
                    )
                        ->badge()
                        ->searchable(),

                    TextColumn::make(
                        'status',
                    )
                        ->badge()
                        ->sortable(),

                    IconColumn::make(
                        'fallback_used',
                    )
                        ->label(
                            'Fallback',
                        )
                        ->boolean(),

                    TextColumn::make(
                        'latency_ms',
                    )
                        ->label(
                            'Latency',
                        )
                        ->suffix(
                            ' ms',
                        )
                        ->sortable(),

                    TextColumn::make(
                        'input_characters',
                    )
                        ->label(
                            'Input',
                        )
                        ->numeric()
                        ->toggleable(
                            isToggledHiddenByDefault:
                                true,
                        ),

                    TextColumn::make(
                        'output_characters',
                    )
                        ->label(
                            'Output',
                        )
                        ->numeric()
                        ->toggleable(
                            isToggledHiddenByDefault:
                                true,
                        ),

                    TextColumn::make(
                        'created_at',
                    )
                        ->label(
                            'Recorded',
                        )
                        ->dateTime(
                            'd M Y h:i A',
                        )
                        ->sortable(),
                ],
            )
            ->filters(
                [
                    SelectFilter::make(
                        'requested_provider',
                    )
                        ->label(
                            'Requested Provider',
                        )
                        ->options(
                            [
                                'local' =>
                                    'Local',

                                'openai' =>
                                    'OpenAI',

                                'gemini' =>
                                    'Gemini',
                            ],
                        ),

                    SelectFilter::make(
                        'provider',
                    )
                        ->label(
                            'Actual Provider',
                        )
                        ->options(
                            [
                                'local' =>
                                    'Local',
                            ],
                        ),

                    SelectFilter::make(
                        'source',
                    )
                        ->options(
                            [
                                'knowledge_base' =>
                                    'Knowledge Base',

                                'local_rules' =>
                                    'Local Rules',

                                'safe_fallback' =>
                                    'Safe Fallback',
                            ],
                        ),

                    SelectFilter::make(
                        'status',
                    )
                        ->options(
                            [
                                'success' =>
                                    'Success',

                                'fallback' =>
                                    'Fallback',

                                'failed' =>
                                    'Failed',
                            ],
                        ),

                    TernaryFilter::make(
                        'fallback_used',
                    )
                        ->label(
                            'Fallback Used',
                        ),
                ],
            )
            ->recordActions(
                [
                    ViewAction::make(),
                ],
            );
    }
}
