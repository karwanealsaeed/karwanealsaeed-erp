<?php

namespace App\Filament\Resources\SupplierPayments\Schemas;

use App\Models\AccountHead;
use App\Models\Supplier;
use App\Models\SupplierBill;
use App\Models\SupplierPayment;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class SupplierPaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Supplier Payment')
                    ->columns(3)
                    ->schema([
                        TextInput::make('payment_no')
                            ->label('Payment No')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto Generated'),

                        Select::make('supplier_bill_id')
                            ->label('Approved Supplier Bill')
                            ->options(fn (): array =>
                                SupplierBill::query()
                                    ->with('supplier')
                                    ->where(
                                        'approval_status',
                                        'Approved'
                                    )
                                    ->whereIn('status', [
                                        'Unpaid',
                                        'Partially Paid',
                                    ])
                                    ->where(
                                        'balance_amount',
                                        '>',
                                        0
                                    )
                                    ->orderByDesc('bill_date')
                                    ->get()
                                    ->mapWithKeys(
                                        fn (SupplierBill $bill): array => [
                                            $bill->id =>
                                                $bill->bill_no
                                                . ' - '
                                                . (
                                                    $bill->supplier
                                                        ?->display_name
                                                    ?: 'Supplier'
                                                )
                                                . ' - Balance PKR '
                                                . number_format(
                                                    (float)
                                                        $bill->balance_amount,
                                                    2
                                                ),
                                        ]
                                    )
                                    ->toArray()
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required()
                            ->native(false)
                            ->afterStateUpdated(
                                function (
                                    $state,
                                    Set $set
                                ): void {
                                    $bill = SupplierBill::query()
                                        ->find($state);

                                    $set(
                                        'supplier_id',
                                        $bill?->supplier_id
                                    );

                                    $set(
                                        'available_balance',
                                        $bill
                                            ? (float)
                                                $bill->balance_amount
                                            : 0
                                    );
                                }
                            ),

                        Select::make('supplier_id')
                            ->label('Supplier')
                            ->options(fn (): array =>
                                Supplier::query()
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
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->native(false),

                        TextInput::make('available_balance')
                            ->label('Current Bill Balance')
                            ->numeric()
                            ->prefix('PKR')
                            ->disabled()
                            ->dehydrated(false)
                            ->afterStateHydrated(
                                function (
                                    TextInput $component,
                                    ?SupplierPayment $record
                                ): void {
                                    $component->state(
                                        $record?->bill
                                            ? (float)
                                                $record->bill
                                                    ->balance_amount
                                            : 0
                                    );
                                }
                            ),

                        DatePicker::make('payment_date')
                            ->label('Payment Date')
                            ->default(today())
                            ->required(),

                        TextInput::make('amount')
                            ->label('Payment Amount')
                            ->numeric()
                            ->prefix('PKR')
                            ->minValue(0.01)
                            ->required(),

                        Select::make('payment_method')
                            ->label('Payment Method')
                            ->options([
                                'Cash' => 'Cash',
                                'Bank' => 'Bank Transfer',
                                'Cheque' => 'Cheque',
                                'JazzCash' => 'JazzCash',
                                'EasyPaisa' => 'EasyPaisa',
                                'Credit Card' => 'Credit Card',
                                'Adjustment' => 'Adjustment',
                            ])
                            ->default('Cash')
                            ->required()
                            ->native(false),

                        Select::make('payment_account_head_id')
                            ->label('Cash / Bank / Wallet Account')
                            ->options(fn (): array =>
                                AccountHead::query()
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
                                    ->toArray()
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->helperText(
                                'Cash payment ke liye Cash Account aur Bank payment ke liye relevant Bank GL select karein.'
                            ),

                        TextInput::make('reference_no')
                            ->label('Reference No')
                            ->maxLength(100),

                        Select::make('status')
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
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}