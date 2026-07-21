<?php

namespace App\Filament\Resources\VisaApplications\Schemas;

use Illuminate\Support\HtmlString;

use Filament\Forms\Components\Placeholder;

use App\Services\PassportValidityService;

use App\Services\CustomerPassportService;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class VisaApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('application_no')
                    ->label('Application No')
                    ->disabled()
                    ->dehydrated(),

                Select::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required()
                    ->afterStateUpdated(function ($state, Set $set) {

                        $customer = \App\Models\Customer::find($state);

                        if ($customer) {
                            $set('passport_no', $customer->passport_no);
                        }

                    })
    ->afterStateUpdated(function (Set $set, mixed $state): void {
        $defaults = app(
            CustomerPassportService::class,
        )->defaultsForCustomer(
            filled($state)
                ? (int) $state
                : null,
        );

        $set(
            'passport_no',
            $defaults['passport_no'],
        );

        $set(
            'passport_expiry_date',
            $defaults['passport_expiry_date'],
        );
    }),

                Select::make('country_id')
                    ->label('Country')
                    ->relationship('country', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('visa_type_id')
                    ->label('Visa Type')
                    ->relationship('visaType', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('passport_no')
                    ->label('Passport Number')
                    ->required(),

    DatePicker::make('passport_expiry_date')
        ->label('Passport Expiry Date')
        ->native(false)
        ->live()
        ->required(
            fn (Get $get): bool =>
                ($get('status') ?? 'Draft')
                    !== 'Draft',
        )
        ->helperText(
            'Latest Passport document se auto-fill hoti hai. Zarurat par manually update karein.',
        ),

    TextInput::make(
        'required_passport_validity_months',
    )
        ->label('Required Validity After Journey')
        ->numeric()
        ->minValue(0)
        ->maxValue(24)
        ->default(
            PassportValidityService
                ::DEFAULT_VALIDITY_MONTHS,
        )
        ->suffix('months')
        ->required()
        ->live()
        ->helperText(
            'International safety default 6 months hai. Destination rule ke mutabiq change ki ja sakti hai.',
        ),

    Placeholder::make(
        'passport_validity_preview',
    )
        ->label('Passport Validity Check')
        ->content(
            function (Get $get): HtmlString {
                $months = (int) (
                    $get(
                        'required_passport_validity_months',
                    )
                    ?: PassportValidityService
                        ::DEFAULT_VALIDITY_MONTHS
                );

                $result = app(
                    PassportValidityService::class,
                )->evaluate(
                    passportExpiry:
                        $get(
                            'passport_expiry_date',
                        ),
                    travelDate:
                        $get('travel_date'),
                    returnDate:
                        $get('return_date'),
                    requiredMonths:
                        $months,
                );

                $style = match (
                    $result['severity']
                ) {
                    'success' =>
                        'background:#ecfdf5;'
                        .'border:1px solid #10b981;'
                        .'color:#065f46;',

                    'warning' =>
                        'background:#fffbeb;'
                        .'border:1px solid #f59e0b;'
                        .'color:#92400e;',

                    default =>
                        'background:#fef2f2;'
                        .'border:1px solid #ef4444;'
                        .'color:#991b1b;',
                };

                $requiredUntil =
                    $result['required_until']
                        ?->format('d M Y');

                $requiredText =
                    $requiredUntil
                        ? '<div style="margin-top:6px">'
                            .'Passport required until: '
                            .'<strong>'
                            .e($requiredUntil)
                            .'</strong></div>'
                        : '';

                return new HtmlString(
                    '<div style="'
                    .$style
                    .'padding:12px 14px;'
                    .'border-radius:10px;'
                    .'font-weight:600;'
                    .'line-height:1.5">'
                    .'<div style="font-weight:800">'
                    .e($result['status'])
                    .'</div>'
                    .'<div>'
                    .e($result['message'])
                    .'</div>'
                    .$requiredText
                    .'</div>',
                );
            },
        )
        ->columnSpanFull(),

                DatePicker::make(
                    'visa_issue_date',
                )
                    ->label(
                        'Visa Issue Date',
                    )
                    ->native(
                        false,
                    )
                    ->helperText(
                        'Issued/approved Visa ki actual issue date.',
                    ),

                DatePicker::make(
                    'visa_expiry_date',
                )
                    ->label(
                        'Visa Expiry Date',
                    )
                    ->native(
                        false,
                    )
                    ->afterOrEqual(
                        'visa_issue_date',
                    )
                    ->helperText(
                        'Expiry alerts isi date ke mutabiq generate honge.',
                    ),

                Select::make('passport_status')
                    ->label('Passport Status')
                    ->options([
                        'Pending' => 'Pending',
                        'Received' => 'Received',
                        'Embassy' => 'Embassy',
                        'Ready' => 'Ready',
                        'Returned' => 'Returned',
                    ])
                    ->default('Pending')
                    ->required(),

                DatePicker::make('passport_received_date')
                    ->label('Passport Received Date'),

                DatePicker::make('passport_return_date')
                    ->label('Passport Return Date'),

                DatePicker::make('travel_date')
                    ->label('Travel Date')
        ->live(),

                DatePicker::make('return_date')
                    ->label('Return Date')
            ->live(),

                TextInput::make('visa_fee')
                    ->label('Visa Fee')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->prefix('PKR')
                    ->live()
                    ->dehydrateStateUsing(fn ($state) => $state ?: 0)
                    ->afterStateUpdated(function (Get $get, Set $set) {

                        $visa = (float) ($get('visa_fee') ?? 0);
                        $service = (float) ($get('service_fee') ?? 0);
                        $paid = (float) ($get('paid_amount') ?? 0);

                        $total = $visa + $service;

                        $set('total_amount', $total);
                        $set('balance_amount', max($total - $paid, 0));
                    }),

                TextInput::make('service_fee')
                    ->label('Service Fee')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->prefix('PKR')
                    ->live()
                    ->dehydrateStateUsing(fn ($state) => $state ?: 0)
                    ->afterStateUpdated(function (Get $get, Set $set) {

                        $visa = (float) ($get('visa_fee') ?? 0);
                        $service = (float) ($get('service_fee') ?? 0);
                        $paid = (float) ($get('paid_amount') ?? 0);

                        $total = $visa + $service;

                        $set('total_amount', $total);
                        $set('balance_amount', max($total - $paid, 0));
                    }),

                TextInput::make('total_amount')
                    ->label('Total Amount')
                    ->numeric()
                    ->prefix('PKR')
                    ->disabled()
                    ->dehydrated(),

                TextInput::make('paid_amount')
                    ->label('Paid Amount')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->prefix('PKR')
                    ->live()
                    ->dehydrateStateUsing(fn ($state) => $state ?: 0)
                    ->afterStateUpdated(function (Get $get, Set $set) {

                        $total = (float) ($get('total_amount') ?? 0);
                        $paid = (float) ($get('paid_amount') ?? 0);

                        $set('balance_amount', max($total - $paid, 0));
                    }),

                TextInput::make('balance_amount')
                    ->label('Balance Amount')
                    ->numeric()
                    ->prefix('PKR')
                    ->disabled()
                    ->dehydrated()
                    ->default(0),

                Select::make('status')
                    ->label('Visa Status')
                    ->options([
                        'Draft' => 'Draft',
                        'Applied' => 'Applied',
                        'Processing' => 'Processing',
                        'Approved' => 'Approved',
                        'Rejected' => 'Rejected',
                        'Delivered' => 'Delivered',
                    ])
                    ->default('Draft')
                    ->required(),

                Textarea::make('notes')
                    ->label('Notes')
                    ->rows(4)
                    ->columnSpanFull(),

            ]);
    }
}
