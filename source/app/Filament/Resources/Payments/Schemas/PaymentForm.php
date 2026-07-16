<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Models\VisaApplication;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('visa_application_id')
                    ->label('Application No')
                    ->relationship('visaApplication', 'application_no')
                    ->getOptionLabelFromRecordUsing(fn (VisaApplication $record): string => $record->application_no . ' - ' . ($record->customer?->name ?? '-'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateHydrated(function (?int $state, Set $set): void {
                        self::setApplicationInfo($state, $set);
                    })
                    ->afterStateUpdated(function (?int $state, Set $set): void {
                        self::setApplicationInfo($state, $set);
                    }),

                TextInput::make('customer_name')
                    ->label('Customer')
                    ->disabled()
                    ->dehydrated(false)
                    ->placeholder('Application select karen'),

                TextInput::make('application_balance')
                    ->label('Remaining Balance')
                    ->disabled()
                    ->dehydrated(false)
                    ->prefix('PKR')
                    ->placeholder('0'),

                DatePicker::make('payment_date')
                    ->label('Payment Date')
                    ->native(false)
                    ->default(now())
                    ->required(),

                TextInput::make('amount')
                    ->label('Amount')
                    ->numeric()
                    ->prefix('PKR')
                    ->required(),

                Select::make('payment_method')
                    ->label('Payment Method')
                    ->options([
                        'Cash' => 'Cash',
                        'Bank' => 'Bank',
                        'JazzCash' => 'JazzCash',
                        'EasyPaisa' => 'EasyPaisa',
                        'Credit Card' => 'Credit Card',
                        'Cheque' => 'Cheque',
                    ])
                    ->default('Cash')
                    ->required(),

                TextInput::make('reference_no')
                    ->label('Reference No')
                    ->maxLength(255),

                Textarea::make('notes')
                    ->label('Notes')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }

    private static function setApplicationInfo(?int $applicationId, Set $set): void
    {
        if (! $applicationId) {
            $set('customer_name', null);
            $set('application_balance', null);

            return;
        }

        $application = VisaApplication::query()
            ->with('customer')
            ->find($applicationId);

        if (! $application) {
            $set('customer_name', null);
            $set('application_balance', null);

            return;
        }

        $set('customer_name', $application->customer?->name ?? '-');
        $set('application_balance', number_format((float) $application->balance_amount, 2));
    }
}