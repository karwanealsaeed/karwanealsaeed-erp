<?php

namespace App\Filament\Resources\Invoices\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('invoice_no')
                    ->label('Invoice No')
                    ->disabled()
                    ->dehydrated(),

                Select::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                DatePicker::make('invoice_date')
                    ->label('Invoice Date')
                    ->default(now())
                    ->required(),

                DatePicker::make('due_date')
                    ->label('Due Date'),

                Select::make('service_type')
                    ->label('Service Type')
                    ->options([
                        'Visa' => 'Visa',
                        'Flight' => 'Flight',
                        'Umrah' => 'Umrah',
                        'Hotel' => 'Hotel',
                        'Other' => 'Other',
                    ])
                    ->default('Other')
                    ->required(),

                TextInput::make('service_reference')
                    ->label('Service Reference')
                    ->placeholder('Example: VA-00001 / FB-00001 / UB-00001 / HB-00001')
                    ->maxLength(255),

                TextInput::make('sub_total')
                    ->label('Sub Total')
                    ->numeric()
                    ->prefix('PKR')
                    ->default(0)
                    ->required()
                    ->live()
                    ->dehydrateStateUsing(fn ($state) => $state ?: 0)
                    ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateAmounts($get, $set)),

                TextInput::make('discount')
                    ->label('Discount')
                    ->numeric()
                    ->prefix('PKR')
                    ->default(0)
                    ->required()
                    ->live()
                    ->dehydrateStateUsing(fn ($state) => $state ?: 0)
                    ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateAmounts($get, $set)),

                TextInput::make('tax')
                    ->label('Tax')
                    ->numeric()
                    ->prefix('PKR')
                    ->default(0)
                    ->required()
                    ->live()
                    ->dehydrateStateUsing(fn ($state) => $state ?: 0)
                    ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateAmounts($get, $set)),

                TextInput::make('total_amount')
                    ->label('Total Amount')
                    ->numeric()
                    ->prefix('PKR')
                    ->disabled()
                    ->dehydrated()
                    ->default(0),

                TextInput::make('paid_amount')
                    ->label('Paid Amount')
                    ->numeric()
                    ->prefix('PKR')
                    ->default(0)
                    ->required()
                    ->live()
                    ->dehydrateStateUsing(fn ($state) => $state ?: 0)
                    ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateAmounts($get, $set)),

                TextInput::make('balance_amount')
                    ->label('Balance Amount')
                    ->numeric()
                    ->prefix('PKR')
                    ->disabled()
                    ->dehydrated()
                    ->default(0),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'Draft' => 'Draft',
                        'Unpaid' => 'Unpaid',
                        'Partially Paid' => 'Partially Paid',
                        'Paid' => 'Paid',
                        'Cancelled' => 'Cancelled',
                    ])
                    ->default('Draft')
                    ->required(),

                Textarea::make('notes')
                    ->label('Notes')
                    ->rows(4)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    protected static function calculateAmounts(Get $get, Set $set): void
    {
        $subTotal = (float) ($get('sub_total') ?? 0);
        $discount = (float) ($get('discount') ?? 0);
        $tax = (float) ($get('tax') ?? 0);
        $paidAmount = (float) ($get('paid_amount') ?? 0);

        $totalAmount = max(($subTotal - $discount) + $tax, 0);

        $set('total_amount', $totalAmount);
        $set('balance_amount', max($totalAmount - $paidAmount, 0));
    }
}
