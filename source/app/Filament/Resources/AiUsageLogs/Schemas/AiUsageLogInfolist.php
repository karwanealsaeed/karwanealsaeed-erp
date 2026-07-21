<?php

declare(strict_types=1);

namespace App\Filament\Resources\AiUsageLogs\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AiUsageLogInfolist
{
    public static function configure(
        Schema $schema,
    ): Schema {
        return $schema
            ->components(
                [
                    Section::make(
                        'Request Overview',
                    )
                        ->columns(
                            3,
                        )
                        ->schema(
                            [
                                TextEntry::make(
                                    'request_uuid',
                                )
                                    ->label(
                                        'Request ID',
                                    )
                                    ->copyable()
                                    ->columnSpanFull(),

                                TextEntry::make(
                                    'customer.name',
                                )
                                    ->label(
                                        'Customer',
                                    )
                                    ->placeholder(
                                        'Public visitor',
                                    ),

                                TextEntry::make(
                                    'requested_provider',
                                )
                                    ->label(
                                        'Requested Provider',
                                    )
                                    ->badge(),

                                TextEntry::make(
                                    'provider',
                                )
                                    ->label(
                                        'Actual Provider',
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

                                TextEntry::make(
                                    'source',
                                )
                                    ->label(
                                        'Answer Source',
                                    )
                                    ->badge(),

                                TextEntry::make(
                                    'status',
                                )
                                    ->badge(),

                                IconEntry::make(
                                    'fallback_used',
                                )
                                    ->label(
                                        'Fallback Used',
                                    )
                                    ->boolean(),

                                TextEntry::make(
                                    'latency_ms',
                                )
                                    ->label(
                                        'Latency',
                                    )
                                    ->suffix(
                                        ' ms',
                                    ),

                                TextEntry::make(
                                    'language',
                                )
                                    ->placeholder(
                                        '-',
                                    ),

                                TextEntry::make(
                                    'category',
                                )
                                    ->placeholder(
                                        '-',
                                    ),

                                TextEntry::make(
                                    'created_at',
                                )
                                    ->label(
                                        'Recorded At',
                                    )
                                    ->dateTime(
                                        'd M Y h:i:s A',
                                    ),
                            ],
                        ),

                    Section::make(
                        'Privacy-Safe Usage',
                    )
                        ->description(
                            'Raw user prompt aur AI reply is log mein store nahi hote. Sirf lengths aur technical outcome record hota hai.',
                        )
                        ->columns(
                            2,
                        )
                        ->schema(
                            [
                                TextEntry::make(
                                    'input_characters',
                                )
                                    ->label(
                                        'Input Characters',
                                    )
                                    ->numeric(),

                                TextEntry::make(
                                    'output_characters',
                                )
                                    ->label(
                                        'Output Characters',
                                    )
                                    ->numeric(),

                                TextEntry::make(
                                    'error_code',
                                )
                                    ->label(
                                        'Error Code',
                                    )
                                    ->placeholder(
                                        'No error',
                                    ),

                                TextEntry::make(
                                    'error_message',
                                )
                                    ->label(
                                        'Safe Error Summary',
                                    )
                                    ->placeholder(
                                        'No error',
                                    )
                                    ->columnSpanFull(),
                            ],
                        ),
                ],
            );
    }
}
