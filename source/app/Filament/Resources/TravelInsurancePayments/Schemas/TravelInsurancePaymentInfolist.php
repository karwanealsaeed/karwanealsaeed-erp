<?php

declare(strict_types=1);

namespace App\Filament\Resources\TravelInsurancePayments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TravelInsurancePaymentInfolist
{
    public static function configure(
        Schema $schema,
    ): Schema {
        return $schema
            ->components(
                [
                    Section::make(
                        'Payment Summary',
                    )
                        ->schema(
                            [
                                TextEntry::make(
                                    'payment_no',
                                )
                                    ->label(
                                        'Payment No.',
                                    )
                                    ->copyable(),

                                TextEntry::make(
                                    'policy.policy_no',
                                )
                                    ->label(
                                        'Policy No.',
                                    ),

                                TextEntry::make(
                                    'policy.customer.name',
                                )
                                    ->label(
                                        'Customer',
                                    ),

                                TextEntry::make(
                                    'payment_date',
                                )
                                    ->date(
                                        'd M Y',
                                    ),

                                TextEntry::make(
                                    'amount',
                                )
                                    ->formatStateUsing(
                                        fn (
                                            mixed $state,
                                            $record,
                                        ): string =>
                                            $record->policy?->currency
                                            .' '
                                            .number_format(
                                                (float) $state,
                                                2,
                                            ),
                                    ),

                                TextEntry::make(
                                    'payment_method',
                                ),

                                TextEntry::make(
                                    'reference_no',
                                )
                                    ->label(
                                        'Reference No.',
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
                                                'Approved' =>
                                                    'success',

                                                'Rejected' =>
                                                    'danger',

                                                default =>
                                                    'warning',
                                            },
                                    ),

                                TextEntry::make(
                                    'posting_status',
                                )
                                    ->label(
                                        'Accounting Status',
                                    )
                                    ->badge()
                                    ->color(
                                        fn (string $state): string =>
                                            $state === 'Posted'
                                                ? 'success'
                                                : 'gray',
                                    ),

                                TextEntry::make(
                                    'accountVoucher.voucher_no',
                                )
                                    ->label(
                                        'Accounting Voucher',
                                    )
                                    ->placeholder(
                                        '-',
                                    ),

                                TextEntry::make(
                                    'approvedBy.name',
                                )
                                    ->label(
                                        'Reviewed By',
                                    )
                                    ->placeholder(
                                        '-',
                                    ),

                                TextEntry::make(
                                    'approved_at',
                                )
                                    ->label(
                                        'Reviewed At',
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
                        'Receipt and Notes',
                    )
                        ->schema(
                            [
                                TextEntry::make(
                                    'receipt_name',
                                )
                                    ->label(
                                        'Receipt File',
                                    )
                                    ->placeholder(
                                        'No receipt uploaded',
                                    ),

                                TextEntry::make(
                                    'receipt_mime',
                                )
                                    ->label(
                                        'File Type',
                                    )
                                    ->placeholder(
                                        '-',
                                    ),

                                TextEntry::make(
                                    'receipt_size',
                                )
                                    ->label(
                                        'File Size',
                                    )
                                    ->formatStateUsing(
                                        fn (
                                            mixed $state,
                                        ): string =>
                                            filled(
                                                $state,
                                            )
                                                ? number_format(
                                                    (float) $state / 1024,
                                                    2,
                                                ).' KB'
                                                : '-',
                                    ),

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
                            3,
                        ),
                ],
            );
    }
}