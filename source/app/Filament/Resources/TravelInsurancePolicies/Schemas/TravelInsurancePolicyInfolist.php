<?php

declare(strict_types=1);

namespace App\Filament\Resources\TravelInsurancePolicies\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TravelInsurancePolicyInfolist
{
    public static function configure(
        Schema $schema,
    ): Schema {
        return $schema
            ->components(
                [
                    Section::make(
                        'Policy Summary',
                    )
                        ->schema(
                            [
                                TextEntry::make(
                                    'policy_no',
                                )
                                    ->label(
                                        'Policy No.',
                                    )
                                    ->copyable(),

                                TextEntry::make(
                                    'customer.name',
                                )
                                    ->label(
                                        'Customer',
                                    ),

                                TextEntry::make(
                                    'provider.name',
                                )
                                    ->label(
                                        'Provider',
                                    )
                                    ->placeholder(
                                        '-',
                                    ),

                                TextEntry::make(
                                    'invoice.invoice_no',
                                )
                                    ->label(
                                        'Invoice',
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

                                                'Expired' =>
                                                    'warning',

                                                'Cancelled',
                                                'Rejected' =>
                                                    'danger',

                                                default =>
                                                    'gray',
                                            },
                                    ),

                                TextEntry::make(
                                    'approval_status',
                                )
                                    ->badge()
                                    ->color(
                                        fn (string $state): string =>
                                            match ($state) {
                                                'Approved' =>
                                                    'success',

                                                'Rejected' =>
                                                    'danger',

                                                default =>
                                                    'warning',
                                            },
                                    ),

                                TextEntry::make(
                                    'payment_status',
                                )
                                    ->badge()
                                    ->color(
                                        fn (string $state): string =>
                                            match ($state) {
                                                'Paid' =>
                                                    'success',

                                                'Partial' =>
                                                    'warning',

                                                default =>
                                                    'danger',
                                            },
                                    ),

                                TextEntry::make(
                                    'approvedBy.name',
                                )
                                    ->label(
                                        'Approved By',
                                    )
                                    ->placeholder(
                                        '-',
                                    ),

                                TextEntry::make(
                                    'approved_at',
                                )
                                    ->dateTime(
                                        'd M Y, h:i A',
                                    )
                                    ->placeholder(
                                        '-',
                                    ),
                            ],
                        )
                        ->columns(
                            3,
                        ),

                    Section::make(
                        'Traveller and Coverage',
                    )
                        ->schema(
                            [
                                TextEntry::make(
                                    'traveller_name',
                                ),

                                TextEntry::make(
                                    'passport_no',
                                )
                                    ->placeholder(
                                        '-',
                                    ),

                                TextEntry::make(
                                    'date_of_birth',
                                )
                                    ->date(
                                        'd M Y',
                                    )
                                    ->placeholder(
                                        '-',
                                    ),

                                TextEntry::make(
                                    'beneficiary_name',
                                )
                                    ->placeholder(
                                        '-',
                                    ),

                                TextEntry::make(
                                    'beneficiary_relationship',
                                )
                                    ->placeholder(
                                        '-',
                                    ),

                                TextEntry::make(
                                    'destination_country',
                                ),

                                TextEntry::make(
                                    'trip_type',
                                ),

                                TextEntry::make(
                                    'coverage_type',
                                ),

                                TextEntry::make(
                                    'coverage_amount',
                                )
                                    ->formatStateUsing(
                                        fn (
                                            mixed $state,
                                            $record,
                                        ): string =>
                                            $record->coverage_currency
                                            .' '
                                            .number_format(
                                                (float) $state,
                                                2,
                                            ),
                                    ),

                                TextEntry::make(
                                    'travel_start_date',
                                )
                                    ->date(
                                        'd M Y',
                                    ),

                                TextEntry::make(
                                    'travel_end_date',
                                )
                                    ->date(
                                        'd M Y',
                                    ),

                                TextEntry::make(
                                    'policy_start_date',
                                )
                                    ->date(
                                        'd M Y',
                                    )
                                    ->placeholder(
                                        '-',
                                    ),

                                TextEntry::make(
                                    'policy_end_date',
                                )
                                    ->date(
                                        'd M Y',
                                    )
                                    ->placeholder(
                                        '-',
                                    ),
                            ],
                        )
                        ->columns(
                            3,
                        ),

                    Section::make(
                        'Financial Summary',
                    )
                        ->schema(
                            [
                                TextEntry::make(
                                    'premium_amount',
                                )
                                    ->formatStateUsing(
                                        fn (
                                            mixed $state,
                                            $record,
                                        ): string =>
                                            $record->currency
                                            .' '
                                            .number_format(
                                                (float) $state,
                                                2,
                                            ),
                                    ),

                                TextEntry::make(
                                    'supplier_cost',
                                )
                                    ->formatStateUsing(
                                        fn (
                                            mixed $state,
                                            $record,
                                        ): string =>
                                            $record->currency
                                            .' '
                                            .number_format(
                                                (float) $state,
                                                2,
                                            ),
                                    ),

                                TextEntry::make(
                                    'discount',
                                )
                                    ->formatStateUsing(
                                        fn (
                                            mixed $state,
                                            $record,
                                        ): string =>
                                            $record->currency
                                            .' '
                                            .number_format(
                                                (float) $state,
                                                2,
                                            ),
                                    ),

                                TextEntry::make(
                                    'tax',
                                )
                                    ->formatStateUsing(
                                        fn (
                                            mixed $state,
                                            $record,
                                        ): string =>
                                            $record->currency
                                            .' '
                                            .number_format(
                                                (float) $state,
                                                2,
                                            ),
                                    ),

                                TextEntry::make(
                                    'total_amount',
                                )
                                    ->formatStateUsing(
                                        fn (
                                            mixed $state,
                                            $record,
                                        ): string =>
                                            $record->currency
                                            .' '
                                            .number_format(
                                                (float) $state,
                                                2,
                                            ),
                                    ),

                                TextEntry::make(
                                    'paid_amount',
                                )
                                    ->formatStateUsing(
                                        fn (
                                            mixed $state,
                                            $record,
                                        ): string =>
                                            $record->currency
                                            .' '
                                            .number_format(
                                                (float) $state,
                                                2,
                                            ),
                                    ),

                                TextEntry::make(
                                    'balance_amount',
                                )
                                    ->formatStateUsing(
                                        fn (
                                            mixed $state,
                                            $record,
                                        ): string =>
                                            $record->currency
                                            .' '
                                            .number_format(
                                                (float) $state,
                                                2,
                                            ),
                                    ),

                                TextEntry::make(
                                    'profit_amount',
                                )
                                    ->formatStateUsing(
                                        fn (
                                            mixed $state,
                                            $record,
                                        ): string =>
                                            $record->currency
                                            .' '
                                            .number_format(
                                                (float) $state,
                                                2,
                                            ),
                                    ),
                            ],
                        )
                        ->columns(
                            4,
                        ),

                    Section::make(
                        'Notes and Audit',
                    )
                        ->schema(
                            [
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