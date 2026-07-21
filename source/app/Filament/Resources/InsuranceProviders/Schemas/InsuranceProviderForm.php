<?php

declare(strict_types=1);

namespace App\Filament\Resources\InsuranceProviders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InsuranceProviderForm
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
                        ->description(
                            'Travel insurance company aur supplier linkage manage karein.',
                        )
                        ->schema(
                            [
                                TextInput::make(
                                    'name',
                                )
                                    ->label(
                                        'Provider Name',
                                    )
                                    ->required()
                                    ->maxLength(
                                        255,
                                    ),

                                TextInput::make(
                                    'company_name',
                                )
                                    ->label(
                                        'Registered Company Name',
                                    )
                                    ->maxLength(
                                        255,
                                    ),

                                Select::make(
                                    'supplier_id',
                                )
                                    ->label(
                                        'Linked Supplier',
                                    )
                                    ->relationship(
                                        'supplier',
                                        'name',
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->helperText(
                                        'Accounting supplier record available ho to link karein.',
                                    ),

                                Select::make(
                                    'status',
                                )
                                    ->options(
                                        [
                                            'Active' =>
                                                'Active',

                                            'Inactive' =>
                                                'Inactive',

                                            'Suspended' =>
                                                'Suspended',
                                        ],
                                    )
                                    ->default(
                                        'Active',
                                    )
                                    ->required()
                                    ->native(
                                        false,
                                    ),

                                TextInput::make(
                                    'license_no',
                                )
                                    ->label(
                                        'License / Registration No.',
                                    )
                                    ->maxLength(
                                        255,
                                    ),

                                TextInput::make(
                                    'website',
                                )
                                    ->url()
                                    ->maxLength(
                                        255,
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
                                TextInput::make(
                                    'contact_person',
                                )
                                    ->maxLength(
                                        255,
                                    ),

                                TextInput::make(
                                    'phone',
                                )
                                    ->tel()
                                    ->maxLength(
                                        50,
                                    ),

                                TextInput::make(
                                    'whatsapp',
                                )
                                    ->tel()
                                    ->maxLength(
                                        50,
                                    ),

                                TextInput::make(
                                    'email',
                                )
                                    ->email()
                                    ->maxLength(
                                        255,
                                    ),

                                TextInput::make(
                                    'city',
                                )
                                    ->maxLength(
                                        255,
                                    ),

                                TextInput::make(
                                    'country',
                                )
                                    ->default(
                                        'Pakistan',
                                    )
                                    ->required()
                                    ->maxLength(
                                        255,
                                    ),

                                Textarea::make(
                                    'address',
                                )
                                    ->rows(
                                        3,
                                    )
                                    ->columnSpanFull(),

                                Textarea::make(
                                    'notes',
                                )
                                    ->rows(
                                        4,
                                    )
                                    ->columnSpanFull(),
                            ],
                        )
                        ->columns(
                            2,
                        ),
                ],
            );
    }
}