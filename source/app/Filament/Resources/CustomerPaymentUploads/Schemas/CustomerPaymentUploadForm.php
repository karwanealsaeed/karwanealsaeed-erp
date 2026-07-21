<?php

namespace App\Filament\Resources\CustomerPaymentUploads\Schemas;

use App\Models\AccountHead;
use App\Models\Customer;
use App\Models\CustomerPaymentUpload;
use App\Models\Invoice;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class CustomerPaymentUploadForm
{
    public static function configure(
        Schema $schema
    ): Schema {
        return $schema
            ->components([
                Section::make(
                    'Customer Receipt Information'
                )
                    ->columns(3)
                    ->schema([
                        TextInput::make('upload_no')
                            ->label('Receipt Upload No')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder(
                                'Auto Generated'
                            ),

                        Select::make('customer_id')
                            ->label('Customer / Agent')
                            ->options(
                                fn (): array =>
                                    Customer::query()
                                        ->orderBy('name')
                                        ->get()
                                        ->mapWithKeys(
                                            fn (
                                                Customer $customer
                                            ): array => [
                                                $customer->id =>
                                                    $customer->name
                                                    . ' - '
                                                    . $customer->mobile
                                                    . ' ('
                                                    . $customer
                                                        ->customer_type
                                                    . ')',
                                            ]
                                        )
                                        ->toArray()
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required()
                            ->disabled(
                                fn (
                                    ?CustomerPaymentUpload
                                    $record
                                ): bool =>
                                    $record?->status
                                    === 'Approved'
                            )
                            ->dehydrated()
                            ->native(false),

                        Select::make('receipt_type')
                            ->label('Receipt Type')
                            ->options([
                                'Invoice Payment' =>
                                    'Invoice Payment',

                                'Customer Advance' =>
                                    'Customer Advance',
                            ])
                            ->default(
                                'Invoice Payment'
                            )
                            ->live()
                            ->required()
                            ->disabled(
                                fn (
                                    ?CustomerPaymentUpload
                                    $record
                                ): bool =>
                                    $record?->status
                                        === 'Approved'
                                    && filled(
                                        $record
                                            ->account_voucher_id
                                    )
                            )
                            ->dehydrated()
                            ->native(false),

                        Select::make('invoice_id')
                            ->label('Customer Invoice')
                            ->options(
                                function (
                                    Get $get,
                                    ?CustomerPaymentUpload
                                    $record
                                ): array {
                                    $customerId =
                                        $get('customer_id');

                                    if (! $customerId) {
                                        return [];
                                    }

                                    return Invoice::query()
                                        ->where(
                                            'customer_id',
                                            $customerId
                                        )
                                        ->where(function (
                                            Builder $query
                                        ) use (
                                            $record
                                        ): void {
                                            $query
                                                ->where(function (
                                                    Builder $query
                                                ): void {
                                                    $query
                                                        ->whereIn(
                                                            'status',
                                                            [
                                                                'Unpaid',
                                                                'Partially Paid',
                                                            ]
                                                        )
                                                        ->where(
                                                            'balance_amount',
                                                            '>',
                                                            0
                                                        );
                                                });

                                            if (
                                                $record
                                                    ?->invoice_id
                                            ) {
                                                $query->orWhere(
                                                    'id',
                                                    $record
                                                        ->invoice_id
                                                );
                                            }
                                        })
                                        ->orderByDesc(
                                            'invoice_date'
                                        )
                                        ->get()
                                        ->mapWithKeys(
                                            fn (
                                                Invoice $invoice
                                            ): array => [
                                                $invoice->id =>
                                                    $invoice
                                                        ->invoice_no
                                                    . ' - '
                                                    . $invoice
                                                        ->service_type
                                                    . ' - Balance PKR '
                                                    . number_format(
                                                        (float)
                                                            $invoice
                                                                ->balance_amount,
                                                        2
                                                    ),
                                            ]
                                        )
                                        ->toArray();
                                }
                            )
                            ->searchable()
                            ->preload()
                            ->visible(
                                fn (Get $get): bool =>
                                    $get('receipt_type')
                                    === 'Invoice Payment'
                            )
                            ->required(
                                fn (Get $get): bool =>
                                    $get('receipt_type')
                                    === 'Invoice Payment'
                            )
                            ->disabled(
                                fn (
                                    ?CustomerPaymentUpload
                                    $record
                                ): bool =>
                                    $record?->status
                                        === 'Approved'
                                    && filled(
                                        $record
                                            ->account_voucher_id
                                    )
                            )
                            ->dehydrated()
                            ->native(false),

                        Select::make('service_type')
                            ->label('Service Type')
                            ->options([
                                'Visa' => 'Visa',
                                'Flight' => 'Flight',
                                'Umrah' => 'Umrah',
                                'Hotel' => 'Hotel',
                                'Other' => 'Other',
                            ])
                            ->native(false),

                        TextInput::make('reference_no')
                            ->label('Service Reference')
                            ->maxLength(255)
                            ->disabled(
                                fn (
                                    ?CustomerPaymentUpload
                                    $record
                                ): bool =>
                                    $record?->status
                                    === 'Approved'
                            )
                            ->dehydrated(),

                        TextInput::make('amount')
                            ->label('Receipt Amount')
                            ->numeric()
                            ->prefix('PKR')
                            ->minValue(0.01)
                            ->required()
                            ->disabled(
                                fn (
                                    ?CustomerPaymentUpload
                                    $record
                                ): bool =>
                                    $record?->status
                                    === 'Approved'
                            )
                            ->dehydrated(),

                        Select::make('payment_method')
                            ->label('Payment Method')
                            ->options([
                                'Cash' => 'Cash',
                                'Bank' => 'Bank Transfer',
                                'Cheque' => 'Cheque',
                                'JazzCash' => 'JazzCash',
                                'EasyPaisa' => 'EasyPaisa',
                                'Credit Card' =>
                                    'Credit Card',
                            ])
                            ->required()
                            ->disabled(
                                fn (
                                    ?CustomerPaymentUpload
                                    $record
                                ): bool =>
                                    $record?->status
                                    === 'Approved'
                            )
                            ->dehydrated()
                            ->native(false),

                        DatePicker::make('payment_date')
                            ->label('Payment Date')
                            ->default(today())
                            ->required()
                            ->disabled(
                                fn (
                                    ?CustomerPaymentUpload
                                    $record
                                ): bool =>
                                    $record?->status
                                    === 'Approved'
                            )
                            ->dehydrated(),

                        TextInput::make('transaction_id')
                            ->label(
                                'Transaction / Bank ID'
                            )
                            ->maxLength(255)
                            ->disabled(
                                fn (
                                    ?CustomerPaymentUpload
                                    $record
                                ): bool =>
                                    $record?->status
                                    === 'Approved'
                            )
                            ->dehydrated(),

                        FileUpload::make('receipt_file')
                            ->label('Payment Receipt')
                            ->disk('local')
                            ->visibility('private')
                            ->directory(
                                'customer/payment-receipts'
                            )
                            ->acceptedFileTypes([
                                'application/pdf',
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                            ])
                            ->maxSize(10240)
                            ->openable()
                            ->downloadable()
                            ->columnSpanFull(),
                    ]),

                Section::make('Accounting Posting')
                    ->description(
                        'Invoice Payment: Cash/Bank Debit aur Accounts Receivable Credit. Customer Advance: Cash/Bank Debit aur Customer Advance Credit.'
                    )
                    ->columns(2)
                    ->schema([
                        Select::make(
                            'receipt_account_head_id'
                        )
                            ->label(
                                'Cash / Bank / Wallet Account'
                            )
                            ->options(
                                self::accountHeadOptions()
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(
                                fn (
                                    ?CustomerPaymentUpload
                                    $record
                                ): bool =>
                                    filled(
                                        $record
                                            ?->account_voucher_id
                                    )
                            )
                            ->dehydrated()
                            ->native(false),

                        Select::make(
                            'receivable_account_head_id'
                        )
                            ->label(
                                'Accounts Receivable Account'
                            )
                            ->options(
                                self::accountHeadOptions()
                            )
                            ->searchable()
                            ->preload()
                            ->visible(
                                fn (Get $get): bool =>
                                    $get('receipt_type')
                                    === 'Invoice Payment'
                            )
                            ->required(
                                fn (Get $get): bool =>
                                    $get('receipt_type')
                                    === 'Invoice Payment'
                            )
                            ->different(
                                'receipt_account_head_id'
                            )
                            ->disabled(
                                fn (
                                    ?CustomerPaymentUpload
                                    $record
                                ): bool =>
                                    filled(
                                        $record
                                            ?->account_voucher_id
                                    )
                            )
                            ->dehydrated()
                            ->native(false),

                        Select::make(
                            'advance_account_head_id'
                        )
                            ->label(
                                'Customer Advance Liability Account'
                            )
                            ->options(
                                self::accountHeadOptions()
                            )
                            ->searchable()
                            ->preload()
                            ->visible(
                                fn (Get $get): bool =>
                                    $get('receipt_type')
                                    === 'Customer Advance'
                            )
                            ->required(
                                fn (Get $get): bool =>
                                    $get('receipt_type')
                                    === 'Customer Advance'
                            )
                            ->different(
                                'receipt_account_head_id'
                            )
                            ->disabled(
                                fn (
                                    ?CustomerPaymentUpload
                                    $record
                                ): bool =>
                                    filled(
                                        $record
                                            ?->account_voucher_id
                                    )
                            )
                            ->dehydrated()
                            ->native(false),

                        Select::make('status')
                            ->options([
                                'Pending' => 'Pending',
                                'Approved' => 'Approved',
                                'Rejected' => 'Rejected',
                                'Cancelled' => 'Cancelled',
                            ])
                            ->disabled()
                            ->dehydrated()
                            ->native(false),

                        Toggle::make(
                            'invoice_balance_applied'
                        )
                            ->label(
                                'Invoice Balance Applied'
                            )
                            ->disabled()
                            ->dehydrated(false),

                        Textarea::make(
                            'rejection_reason'
                        )
                            ->label('Rejection Reason')
                            ->disabled()
                            ->dehydrated(false)
                            ->rows(2),

                        Textarea::make('customer_notes')
                            ->label('Customer Notes')
                            ->rows(3),

                        Textarea::make('admin_notes')
                            ->label('Admin Notes')
                            ->rows(3),

                        Textarea::make('void_reason')
                            ->label('Void Reason')
                            ->disabled()
                            ->dehydrated(false)
                            ->rows(2)
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
}