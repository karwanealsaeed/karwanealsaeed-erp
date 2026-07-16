<?php

namespace App\Filament\Resources\SupplierBills\Schemas;

use App\Models\AccountHead;
use App\Models\Supplier;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class SupplierBillForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Supplier Bill Information')
                    ->columns(3)
                    ->schema([
                        TextInput::make('bill_no')
                            ->label('Bill No')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto Generated'),

                        Select::make('supplier_id')
                            ->label('Supplier')
                            ->options(fn (): array =>
                                Supplier::query()
                                    ->where('status', 'Active')
                                    ->orderBy('company_name')
                                    ->orderBy('name')
                                    ->get()
                                    ->mapWithKeys(
                                        fn (Supplier $supplier): array => [
                                            $supplier->id =>
                                                $supplier->display_name,
                                        ]
                                    )
                                    ->toArray()
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),

                        Select::make('service_type')
                            ->label('Service Type')
                            ->options([
                                'Airline' => 'Airline / Ticketing',
                                'Hotel' => 'Hotel',
                                'Visa' => 'Visa',
                                'Transport' => 'Transport',
                                'Umrah' => 'Umrah',
                                'Insurance' => 'Insurance',
                                'Tour' => 'Tour',
                                'Other' => 'Other',
                            ])
                            ->default('Other')
                            ->required()
                            ->native(false),

                        TextInput::make('supplier_invoice_no')
                            ->label('Supplier Invoice No')
                            ->maxLength(100),

                        TextInput::make('reference_no')
                            ->label('Reference No')
                            ->maxLength(100),

                        DatePicker::make('bill_date')
                            ->label('Bill Date')
                            ->default(today())
                            ->required(),

                        DatePicker::make('due_date')
                            ->label('Due Date')
                            ->minDate(
                                fn (Get $get) =>
                                    $get('bill_date')
                            ),

                        Select::make('approval_status')
                            ->label('Approval Status')
                            ->options([
                                'Pending' => 'Pending',
                                'Approved' => 'Approved',
                                'Rejected' => 'Rejected',
                                'Cancelled' => 'Cancelled',
                            ])
                            ->default('Pending')
                            ->disabled()
                            ->dehydrated()
                            ->native(false),

                        Select::make('status')
                            ->label('Payment Status')
                            ->options([
                                'Draft' => 'Draft',
                                'Unpaid' => 'Unpaid',
                                'Partially Paid' =>
                                    'Partially Paid',
                                'Paid' => 'Paid',
                                'Cancelled' => 'Cancelled',
                            ])
                            ->default('Draft')
                            ->disabled()
                            ->dehydrated()
                            ->native(false),

                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Accounting Accounts')
                    ->description(
                        'Approval par balanced GL voucher automatically post hoga.'
                    )
                    ->columns(2)
                    ->schema([
                        Select::make('expense_account_head_id')
                            ->label('Purchase / Expense Account')
                            ->options(self::accountHeadOptions())
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),

                        Select::make('payable_account_head_id')
                            ->label('Supplier Payable Account')
                            ->options(self::accountHeadOptions())
                            ->searchable()
                            ->preload()
                            ->required()
                            ->different(
                                'expense_account_head_id'
                            )
                            ->native(false),
                    ]),

                Section::make('Bill Amounts')
                    ->columns(3)
                    ->schema([
                        TextInput::make('sub_total')
                            ->label('Sub Total')
                            ->numeric()
                            ->prefix('PKR')
                            ->default(0)
                            ->minValue(0)
                            ->required()
                            ->live()
                            ->afterStateUpdated(
                                fn (Get $get, Set $set) =>
                                    self::syncTotals(
                                        $get,
                                        $set
                                    )
                            ),

                        TextInput::make('discount')
                            ->numeric()
                            ->prefix('PKR')
                            ->default(0)
                            ->minValue(0)
                            ->live()
                            ->afterStateUpdated(
                                fn (Get $get, Set $set) =>
                                    self::syncTotals(
                                        $get,
                                        $set
                                    )
                            ),

                        TextInput::make('tax')
                            ->numeric()
                            ->prefix('PKR')
                            ->default(0)
                            ->minValue(0)
                            ->live()
                            ->afterStateUpdated(
                                fn (Get $get, Set $set) =>
                                    self::syncTotals(
                                        $get,
                                        $set
                                    )
                            ),

                        TextInput::make('total_amount')
                            ->label('Total Amount')
                            ->numeric()
                            ->prefix('PKR')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('paid_amount')
                            ->label('Approved Payments')
                            ->numeric()
                            ->prefix('PKR')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('balance_amount')
                            ->label('Outstanding Balance')
                            ->numeric()
                            ->prefix('PKR')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make(
                            'accountVoucher.voucher_no'
                        )
                            ->label('Posted Voucher')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Not Posted'),

                        Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->disabled()
                            ->dehydrated(false)
                            ->rows(2),

                        Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private static function accountHeadOptions(): array
    {
        return AccountHead::query()
            ->where('status', 'Active')
            ->orderBy('code')
            ->get()
            ->mapWithKeys(
                fn (AccountHead $head): array => [
                    $head->id =>
                        $head->code
                        . ' - '
                        . $head->name
                        . ' ('
                        . $head->type
                        . ')',
                ]
            )
            ->toArray();
    }

    private static function syncTotals(
        Get $get,
        Set $set
    ): void {
        $subTotal = max(
            0,
            (float) ($get('sub_total') ?? 0)
        );

        $discount = max(
            0,
            (float) ($get('discount') ?? 0)
        );

        $tax = max(
            0,
            (float) ($get('tax') ?? 0)
        );

        $paid = max(
            0,
            (float) ($get('paid_amount') ?? 0)
        );

        $total = round(
            max(
                0,
                $subTotal - $discount + $tax
            ),
            2
        );

        $set('total_amount', $total);

        $set(
            'balance_amount',
            round(
                max(0, $total - $paid),
                2
            )
        );
    }
}