<?php

declare(strict_types=1);

namespace App\Filament\Resources\InsuranceProviders\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InsuranceProviderInfolist
{
    public static function configure(
        Schema $schema,
    ): Schema {
        return $schema
            ->components(
                [
                    Section::make(
                        'Provider Information',
                    )
                        ->schema(
                            [
                                TextEntry::make(
                                    'name',
                                )
                                    ->label(
                                        'Provider Name',
                                    ),

                                TextEntry::make(
                                    'company_name',
                                )
                                    ->placeholder(
                                        '-',
                                    ),

                                TextEntry::make(
                                    'supplier.name',
                                )
                                    ->label(
                                        'Linked Supplier',
                                    )
                                    ->placeholder(
                                        '-',
                                    ),

                                TextEntry::make(
                                    'license_no',
                                )
                                    ->label(
                                        'License / Registration No.',
                                    )
                                    ->placeholder(
                                        '-',
                                    ),

                                TextEntry::make(
                                    'status',
                                )
                                    ->badge()
                                    ->color(
                                        fn (string $state): string =>
                                            match ($state) {
                                                'Active' =>
                                                    'success',

                                                'Suspended' =>
                                                    'danger',

                                                default =>
                                                    'gray',
                                            },
                                    ),

                                TextEntry::make(
                                    'policies_count',
                                )
                                    ->label(
                                        'Total Policies',
                                    )
                                    ->state(
                                        fn ($record): int =>
                                            $record
                                                ->policies()
                                                ->count(),
                                    ),
                            ],
                        )
                        ->columns(
                            2,
                        ),

                    Section::make(
                        'Contact Details',
                    )
                        ->schema(
                            [
                                TextEntry::make(
                                    'contact_person',
                                )
                                    ->placeholder(
                                        '-',
                                    ),

                                TextEntry::make(
                                    'phone',
                                )
                                    ->placeholder(
                                        '-',
                                    ),

                                TextEntry::make(
                                    'whatsapp',
                                )
                                    ->placeholder(
                                        '-',
                                    ),

                                TextEntry::make(
                                    'email',
                                )
                                    ->placeholder(
                                        '-',
                                    ),

                                TextEntry::make(
                                    'website',
                                )
                                    ->url(
                                        fn ($record): ?string =>
                                            $record->website,
                                    )
                                    ->openUrlInNewTab()
                                    ->placeholder(
                                        '-',
                                    ),

                                TextEntry::make(
                                    'city',
                                )
                                    ->placeholder(
                                        '-',
                                    ),

                                TextEntry::make(
                                    'country',
                                )
                                    ->placeholder(
                                        '-',
                                    ),

                                TextEntry::make(
                                    'address',
                                )
                                    ->placeholder(
                                        '-',
                                    )
                                    ->columnSpanFull(),

                                TextEntry::make(
                                    'notes',
                                )
                                    ->placeholder(
                                        '-',
                                    )
                                    ->columnSpanFull(),

                                TextEntry::make(
                                    'created_at',
                                )
                                    ->dateTime(
                                        'd M Y, h:i A',
                                    ),

                                TextEntry::make(
                                    'updated_at',
                                )
                                    ->dateTime(
                                        'd M Y, h:i A',
                                    ),
                            ],
                        )
                        ->columns(
                            2,
                        ),
                ],
            );
    }
}