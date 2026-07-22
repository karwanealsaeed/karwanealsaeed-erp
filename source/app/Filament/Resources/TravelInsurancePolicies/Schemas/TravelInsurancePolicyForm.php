<?php

declare(strict_types=1);

namespace App\Filament\Resources\TravelInsurancePolicies\Schemas;

use App\Models\Country;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TravelInsurancePolicyForm
{
    public static function configure(
        Schema $schema,
    ): Schema {
        return $schema
            ->components(
                [
                    Section::make(
                        'Policy Information',
                    )
                        ->description(
                            'Customer, provider aur policy ki basic details.',
                        )
                        ->schema(
                            [
                                TextInput::make(
                                    'policy_no',
                                )
                                    ->label(
                                        'Policy No.',
                                    )
                                    ->disabled()
                                    ->dehydrated(
                                        false,
                                    )
                                    ->placeholder(
                                        'Automatically generated',
                                    ),

                                Select::make(
                                    'customer_id',
                                )
                                    ->label(
                                        'Customer',
                                    )
                                    ->relationship(
                                        'customer',
                                        'name',
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make(
                                    'insurance_provider_id',
                                )
                                    ->label(
                                        'Insurance Provider',
                                    )
                                    ->relationship(
                                        'provider',
                                        'name',
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),

                                Select::make(
                                    'invoice_id',
                                )
                                    ->label(
                                        'Linked Invoice',
                                    )
                                    ->relationship(
                                        'invoice',
                                        'invoice_no',
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),

                                Select::make(
                                    'status',
                                )
                                    ->options(
                                        [
                                            'Draft' =>
                                                'Draft',

                                            'Active' =>
                                                'Active',

                                            'Expired' =>
                                                'Expired',

                                            'Cancelled' =>
                                                'Cancelled',

                                            'Rejected' =>
                                                'Rejected',
                                        ],
                                    )
                                    ->default(
                                        'Draft',
                                    )
                                    ->required()
                                    ->native(
                                        false,
                                    ),

                                Select::make(
                                    'approval_status',
                                )
                                    ->options(
                                        [
                                            'Pending' =>
                                                'Pending',

                                            'Approved' =>
                                                'Approved',

                                            'Rejected' =>
                                                'Rejected',
                                        ],
                                    )
                                    ->default(
                                        'Pending',
                                    )
                                    ->required()
                                    ->native(
                                        false,
                                    ),

                                TextInput::make(
                                    'payment_status',
                                )
                                    ->disabled()
                                    ->dehydrated(
                                        false,
                                    )
                                    ->placeholder(
                                        'Calculated automatically',
                                    ),

                                TextInput::make(
                                    'currency',
                                )
                                    ->default(
                                        'PKR',
                                    )
                                    ->required()
                                    ->maxLength(
                                        3,
                                    ),
                            ],
                        )
                        ->columns(
                            2,
                        ),

                    Section::make(
                        'Traveller Details',
                    )
                        ->schema(
                            [
                                TextInput::make(
                                    'traveller_name',
                                )
                                    ->required()
                                    ->maxLength(
                                        255,
                                    ),

                                TextInput::make(
                                    'passport_no',
                                )
                                    ->maxLength(
                                        100,
                                    ),

                                DatePicker::make(
                                    'date_of_birth',
                                )
                                    ->native(
                                        false,
                                    ),

                                TextInput::make(
                                    'beneficiary_name',
                                )
                                    ->maxLength(
                                        255,
                                    ),

                                TextInput::make(
                                    'beneficiary_relationship',
                                )
                                    ->maxLength(
                                        100,
                                    ),

                                Select::make(
                                    'destination_country_id',
                                )
                                    ->label(
                                        'Destination Country',
                                    )
                                    ->options(
                                        fn (): array => Country::query()
                                            ->where(
                                                'status',
                                                true,
                                            )
                                            ->orderBy(
                                                'name',
                                            )
                                            ->pluck(
                                                'name',
                                                'id',
                                            )
                                            ->toArray(),
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ],
                        )
                        ->columns(
                            2,
                        ),

                    Section::make(
                        'Trip and Coverage',
                    )
                        ->schema(
                            [
                                Select::make(
                                    'trip_type',
                                )
                                    ->options(
                                        [
                                            'Single Trip' =>
                                                'Single Trip',

                                            'Multiple Trip' =>
                                                'Multiple Trip',

                                            'Annual' =>
                                                'Annual',
                                        ],
                                    )
                                    ->default(
                                        'Single Trip',
                                    )
                                    ->required()
                                    ->native(
                                        false,
                                    ),

                                Select::make(
                                    'coverage_type',
                                )
                                    ->options(
                                        [
                                            'Basic' =>
                                                'Basic',

                                            'Standard' =>
                                                'Standard',

                                            'Comprehensive' =>
                                                'Comprehensive',

                                            'Schengen' =>
                                                'Schengen',

                                            'Worldwide' =>
                                                'Worldwide',
                                        ],
                                    )
                                    ->default(
                                        'Standard',
                                    )
                                    ->required()
                                    ->native(
                                        false,
                                    ),

                                TextInput::make(
                                    'coverage_amount',
                                )
                                    ->numeric()
                                    ->minValue(
                                        0,
                                    )
                                    ->default(
                                        0,
                                    )
                                    ->required(),

                                TextInput::make(
                                    'coverage_currency',
                                )
                                    ->default(
                                        'USD',
                                    )
                                    ->required()
                                    ->maxLength(
                                        3,
                                    ),

                                DatePicker::make(
                                    'travel_start_date',
                                )
                                    ->required()
                                    ->native(
                                        false,
                                    ),

                                DatePicker::make(
                                    'travel_end_date',
                                )
                                    ->required()
                                    ->native(
                                        false,
                                    ),

                                DatePicker::make(
                                    'policy_start_date',
                                )
                                    ->native(
                                        false,
                                    ),

                                DatePicker::make(
                                    'policy_end_date',
                                )
                                    ->native(
                                        false,
                                    ),
                            ],
                        )
                        ->columns(
                            2,
                        ),

                    Section::make(
                        'Financial Information',
                    )
                        ->description(
                            'Total, profit aur outstanding amounts automatically calculate honge.',
                        )
                        ->schema(
                            [
                                TextInput::make(
                                    'premium_amount',
                                )
                                    ->numeric()
                                    ->minValue(
                                        0,
                                    )
                                    ->default(
                                        0,
                                    )
                                    ->required(),

                                TextInput::make(
                                    'supplier_cost',
                                )
                                    ->numeric()
                                    ->minValue(
                                        0,
                                    )
                                    ->default(
                                        0,
                                    )
                                    ->required(),

                                TextInput::make(
                                    'discount',
                                )
                                    ->numeric()
                                    ->minValue(
                                        0,
                                    )
                                    ->default(
                                        0,
                                    )
                                    ->required(),

                                TextInput::make(
                                    'tax',
                                )
                                    ->numeric()
                                    ->minValue(
                                        0,
                                    )
                                    ->default(
                                        0,
                                    )
                                    ->required(),

                                TextInput::make(
                                    'total_amount',
                                )
                                    ->disabled()
                                    ->dehydrated(
                                        false,
                                    )
                                    ->placeholder(
                                        'Calculated automatically',
                                    ),

                                TextInput::make(
                                    'paid_amount',
                                )
                                    ->disabled()
                                    ->dehydrated(
                                        false,
                                    )
                                    ->placeholder(
                                        'Approved payments only',
                                    ),

                                TextInput::make(
                                    'balance_amount',
                                )
                                    ->disabled()
                                    ->dehydrated(
                                        false,
                                    )
                                    ->placeholder(
                                        'Calculated automatically',
                                    ),

                                TextInput::make(
                                    'profit_amount',
                                )
                                    ->disabled()
                                    ->dehydrated(
                                        false,
                                    )
                                    ->placeholder(
                                        'Calculated automatically',
                                    ),
                            ],
                        )
                        ->columns(
                            2,
                        ),

                    Section::make(
                        'Private Policy Document',
                    )
                        ->description(
                            'Policy PDF ya image private local storage mein save hogi.',
                        )
                        ->schema(
                            [
                                FileUpload::make(
                                    'policy_document_path',
                                )
                                    ->label(
                                        'Policy Document',
                                    )
                                    ->disk(
                                        'local',
                                    )
                                    ->visibility(
                                        'private',
                                    )
                                    ->directory(
                                        'travel-insurance/policies',
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
                                        'policy_document_name',
                                    )
                                    ->helperText(
                                        'Allowed: PDF, JPG, PNG, WEBP. Maximum 10 MB.',
                                    ),
                            ],
                        ),

                    Section::make(
                        'Internal Notes',
                    )
                        ->schema(
                            [
                                Textarea::make(
                                    'notes',
                                )
                                    ->rows(
                                        5,
                                    )
                                    ->columnSpanFull(),
                            ],
                        ),
                ],
            );
    }
}