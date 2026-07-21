<?php

declare(strict_types=1);

namespace App\Filament\Resources\TravelInsurancePayments\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TravelInsurancePaymentForm
{
    public static function configure(
        Schema $schema,
    ): Schema {
        return $schema
            ->components(
                [
                    Section::make(
                        'Payment Information',
                    )
                        ->description(
                            'Policy payment record create karein. Approval separate controlled action se hogi.',
                        )
                        ->schema(
                            [
                                TextInput::make(
                                    'payment_no',
                                )
                                    ->label(
                                        'Payment No.',
                                    )
                                    ->disabled()
                                    ->dehydrated(
                                        false,
                                    )
                                    ->placeholder(
                                        'Automatically generated',
                                    ),

                                Select::make(
                                    'travel_insurance_policy_id',
                                )
                                    ->label(
                                        'Insurance Policy',
                                    )
                                    ->relationship(
                                        'policy',
                                        'policy_no',
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                DatePicker::make(
                                    'payment_date',
                                )
                                    ->default(
                                        today(),
                                    )
                                    ->required()
                                    ->native(
                                        false,
                                    ),

                                TextInput::make(
                                    'amount',
                                )
                                    ->numeric()
                                    ->minValue(
                                        0.01,
                                    )
                                    ->required(),

                                Select::make(
                                    'payment_method',
                                )
                                    ->options(
                                        [
                                            'Cash' =>
                                                'Cash',

                                            'Bank Transfer' =>
                                                'Bank Transfer',

                                            'Card' =>
                                                'Card',

                                            'Cheque' =>
                                                'Cheque',

                                            'Online' =>
                                                'Online',

                                            'Other' =>
                                                'Other',
                                        ],
                                    )
                                    ->default(
                                        'Cash',
                                    )
                                    ->required()
                                    ->native(
                                        false,
                                    ),

                                TextInput::make(
                                    'reference_no',
                                )
                                    ->label(
                                        'Reference No.',
                                    )
                                    ->maxLength(
                                        255,
                                    ),

                                TextInput::make(
                                    'status',
                                )
                                    ->disabled()
                                    ->dehydrated(
                                        false,
                                    )
                                    ->placeholder(
                                        'Pending',
                                    ),

                                TextInput::make(
                                    'posting_status',
                                )
                                    ->label(
                                        'Accounting Status',
                                    )
                                    ->disabled()
                                    ->dehydrated(
                                        false,
                                    )
                                    ->placeholder(
                                        'Pending',
                                    ),

                                Textarea::make(
                                    'notes',
                                )
                                    ->rows(
                                        5,
                                    )
                                    ->columnSpanFull(),
                            ],
                        )
                        ->columns(
                            2,
                        ),

                    Section::make(
                        'Private Receipt Document',
                    )
                        ->description(
                            'Payment receipt private local storage mein save hogi.',
                        )
                        ->schema(
                            [
                                FileUpload::make(
                                    'receipt_path',
                                )
                                    ->label(
                                        'Payment Receipt',
                                    )
                                    ->disk(
                                        'local',
                                    )
                                    ->visibility(
                                        'private',
                                    )
                                    ->directory(
                                        'travel-insurance/receipts',
                                    )
                                    ->acceptedFileTypes(
                                        [
                                            'application/pdf',
                                            'image/jpeg',
                                            'image/png',
                                            'image/webp',
                                        ],
                                    )
                                    ->maxSize(
                                        10240,
                                    )
                                    ->storeFileNamesIn(
                                        'receipt_name',
                                    )
                                    ->helperText(
                                        'Allowed: PDF, JPG, PNG, WEBP. Maximum 10 MB.',
                                    ),
                            ],
                        ),
                ],
            );
    }
}