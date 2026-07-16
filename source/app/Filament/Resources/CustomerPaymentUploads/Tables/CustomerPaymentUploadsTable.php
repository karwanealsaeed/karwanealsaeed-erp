<?php

namespace App\Filament\Resources\CustomerPaymentUploads\Tables;

use App\Models\AccountHead;
use App\Models\CustomerPaymentUpload;
use App\Services\CustomerReceiptPostingService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;
use Throwable;

class CustomerPaymentUploadsTable
{
    public static function configure(
        Table $table
    ): Table {
        return $table
            ->defaultSort('payment_date', 'desc')
            ->columns([
                TextColumn::make('upload_no')
                    ->label('Receipt No')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Customer / Agent')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('receipt_type')
                    ->label('Receipt Type')
                    ->badge()
                    ->color(
                        fn (string $state): string =>
                            $state
                                === 'Invoice Payment'
                                    ? 'info'
                                    : 'warning'
                    ),

                TextColumn::make('invoice.invoice_no')
                    ->label('Invoice')
                    ->badge()
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('payment_date')
                    ->label('Payment Date')
                    ->date('d M Y')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('amount')
                    ->money('PKR')
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label('Method')
                    ->badge()
                    ->placeholder('-'),

                TextColumn::make('transaction_id')
                    ->label('Transaction ID')
                    ->placeholder('-')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make(
                    'receiptAccountHead.code'
                )
                    ->label('Receipt GL')
                    ->badge()
                    ->placeholder('-'),

                TextColumn::make(
                    'receivableAccountHead.code'
                )
                    ->label('A/R GL')
                    ->badge()
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make(
                    'advanceAccountHead.code'
                )
                    ->label('Advance GL')
                    ->badge()
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(
                        fn (string $state): string =>
                            match ($state) {
                                'Approved' => 'success',
                                'Rejected' => 'danger',
                                'Cancelled' => 'gray',
                                default => 'warning',
                            }
                    ),

                TextColumn::make('posting_status')
                    ->label('GL Posting')
                    ->badge()
                    ->color(
                        fn (string $state): string =>
                            match ($state) {
                                'Posted' => 'success',
                                'Missing GL' => 'danger',
                                'Voided' => 'gray',
                                default => 'warning',
                            }
                    ),

                TextColumn::make(
                    'accountVoucher.voucher_no'
                )
                    ->label('GL Voucher')
                    ->badge()
                    ->placeholder('Not Posted'),

                TextColumn::make('approved_at')
                    ->label('Approved At')
                    ->dateTime('d M Y h:i A')
                    ->placeholder('-')
                    ->toggleable(
                        isToggledHiddenByDefault: true
                    ),
            ])
            ->filters([
                SelectFilter::make('customer_id')
                    ->label('Customer / Agent')
                    ->relationship(
                        'customer',
                        'name'
                    )
                    ->searchable()
                    ->preload(),

                SelectFilter::make('receipt_type')
                    ->label('Receipt Type')
                    ->options([
                        'Invoice Payment' =>
                            'Invoice Payment',

                        'Customer Advance' =>
                            'Customer Advance',
                    ]),

                SelectFilter::make('status')
                    ->options([
                        'Pending' => 'Pending',
                        'Approved' => 'Approved',
                        'Rejected' => 'Rejected',
                        'Cancelled' => 'Cancelled',
                    ]),

                SelectFilter::make('payment_method')
                    ->label('Payment Method')
                    ->options([
                        'Cash' => 'Cash',
                        'Bank' => 'Bank Transfer',
                        'Cheque' => 'Cheque',
                        'JazzCash' => 'JazzCash',
                        'EasyPaisa' => 'EasyPaisa',
                        'Credit Card' =>
                            'Credit Card',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),

                Action::make('approve_and_post')
                    ->label('Approve & Post')
                    ->icon(
                        'heroicon-o-check-circle'
                    )
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription(
                        'Receipt approve hote hi balanced Receipt Voucher post hoga aur Invoice Payment ki surat me invoice balance update hoga.'
                    )
                    ->visible(
                        fn (
                            CustomerPaymentUpload $record
                        ): bool =>
                            $record->status
                            === 'Pending'
                    )
                    ->action(function (
                        CustomerPaymentUpload $record
                    ): void {
                        try {
                            app(
                                CustomerReceiptPostingService::class
                            )->approve(
                                $record,
                                (int) auth()->id()
                            );

                            Notification::make()
                                ->title(
                                    'Customer receipt approved and posted'
                                )
                                ->success()
                                ->send();
                        } catch (Throwable $exception) {
                            self::sendError($exception);
                        }
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->schema([
                        Textarea::make('reason')
                            ->label(
                                'Rejection Reason'
                            )
                            ->required()
                            ->rows(4),
                    ])
                    ->visible(
                        fn (
                            CustomerPaymentUpload $record
                        ): bool =>
                            $record->status
                            === 'Pending'
                    )
                    ->action(function (
                        CustomerPaymentUpload $record,
                        array $data
                    ): void {
                        try {
                            app(
                                CustomerReceiptPostingService::class
                            )->reject(
                                $record,
                                (int) auth()->id(),
                                $data['reason']
                            );

                            Notification::make()
                                ->title(
                                    'Customer receipt rejected'
                                )
                                ->danger()
                                ->send();
                        } catch (Throwable $exception) {
                            self::sendError($exception);
                        }
                    }),

                Action::make('resubmit')
                    ->label('Resubmit')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(
                        fn (
                            CustomerPaymentUpload $record
                        ): bool =>
                            $record->status
                            === 'Rejected'
                    )
                    ->action(function (
                        CustomerPaymentUpload $record
                    ): void {
                        try {
                            app(
                                CustomerReceiptPostingService::class
                            )->resubmit($record);

                            Notification::make()
                                ->title(
                                    'Customer receipt resubmitted'
                                )
                                ->success()
                                ->send();
                        } catch (Throwable $exception) {
                            self::sendError($exception);
                        }
                    }),

                Action::make('post_missing_gl')
                    ->label('Post Missing GL')
                    ->icon(
                        'heroicon-o-document-plus'
                    )
                    ->color('warning')
                    ->schema(
                        fn (
                            CustomerPaymentUpload $record
                        ): array => [
                            Toggle::make(
                                'apply_invoice_balance'
                            )
                                ->label(
                                    'Also update invoice paid/balance amounts'
                                )
                                ->helperText(
                                    'Legacy receipt me invoice balance pehle se updated ho to is option ko OFF rakhein.'
                                )
                                ->default(false)
                                ->visible(
                                    $record->receipt_type
                                    === 'Invoice Payment'
                                ),
                        ]
                    )
                    ->visible(
                        fn (
                            CustomerPaymentUpload $record
                        ): bool =>
                            $record->status
                                === 'Approved'
                            && blank(
                                $record
                                    ->account_voucher_id
                            )
                    )
                    ->action(function (
                        CustomerPaymentUpload $record,
                        array $data
                    ): void {
                        try {
                            app(
                                CustomerReceiptPostingService::class
                            )->postMissingApprovedReceipt(
                                receipt: $record,
                                userId:
                                    (int) auth()->id(),

                                applyInvoiceBalance:
                                    (bool) (
                                        $data[
                                            'apply_invoice_balance'
                                        ]
                                        ?? false
                                    ),
                            );

                            Notification::make()
                                ->title(
                                    'Missing receipt voucher posted'
                                )
                                ->success()
                                ->send();
                        } catch (Throwable $exception) {
                            self::sendError($exception);
                        }
                    }),

                Action::make('correct_gl')
                    ->label('Correct GL')
                    ->icon(
                        'heroicon-o-arrow-path-rounded-square'
                    )
                    ->color('warning')
                    ->schema(
                        fn (
                            CustomerPaymentUpload $record
                        ): array => [
                            Select::make(
                                'receipt_account_head_id'
                            )
                                ->label(
                                    'Correct Cash / Bank / Wallet Account'
                                )
                                ->options(
                                    self::accountHeadOptions()
                                )
                                ->default(
                                    $record
                                        ->receipt_account_head_id
                                )
                                ->searchable()
                                ->required()
                                ->native(false),

                            Select::make(
                                'contra_account_head_id'
                            )
                                ->label(
                                    $record->receipt_type
                                    === 'Invoice Payment'
                                        ? 'Correct Accounts Receivable Account'
                                        : 'Correct Customer Advance Account'
                                )
                                ->options(
                                    self::accountHeadOptions()
                                )
                                ->default(
                                    $record->receipt_type
                                    === 'Invoice Payment'
                                        ? $record
                                            ->receivable_account_head_id
                                        : $record
                                            ->advance_account_head_id
                                )
                                ->searchable()
                                ->required()
                                ->different(
                                    'receipt_account_head_id'
                                )
                                ->native(false),
                        ]
                    )
                    ->modalDescription(
                        'Old voucher Cancelled hoga aur corrected balanced voucher post hoga. Invoice balance dobara update nahi hoga.'
                    )
                    ->visible(
                        fn (
                            CustomerPaymentUpload $record
                        ): bool =>
                            $record->status
                                === 'Approved'
                            && filled(
                                $record
                                    ->account_voucher_id
                            )
                    )
                    ->action(function (
                        CustomerPaymentUpload $record,
                        array $data
                    ): void {
                        try {
                            app(
                                CustomerReceiptPostingService::class
                            )->correctPosting(
                                receipt: $record,
                                userId:
                                    (int) auth()->id(),

                                receiptAccountHeadId:
                                    (int) $data[
                                        'receipt_account_head_id'
                                    ],

                                contraAccountHeadId:
                                    (int) $data[
                                        'contra_account_head_id'
                                    ],
                            );

                            Notification::make()
                                ->title(
                                    'Customer receipt GL corrected'
                                )
                                ->success()
                                ->send();
                        } catch (Throwable $exception) {
                            self::sendError($exception);
                        }
                    }),

                Action::make('void_and_reverse')
                    ->label('Void & Reverse')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->schema([
                        Textarea::make('reason')
                            ->label('Void Reason')
                            ->required()
                            ->rows(4),
                    ])
                    ->modalDescription(
                        'Voucher Cancelled hoga. Invoice balance applied hua tha to woh bhi reverse hoga. Reconciled bank entry pehle unmatch karni hogi.'
                    )
                    ->visible(
                        fn (
                            CustomerPaymentUpload $record
                        ): bool =>
                            $record->status
                                === 'Approved'
                            && filled(
                                $record
                                    ->account_voucher_id
                            )
                    )
                    ->action(function (
                        CustomerPaymentUpload $record,
                        array $data
                    ): void {
                        try {
                            app(
                                CustomerReceiptPostingService::class
                            )->voidAndReverse(
                                receipt: $record,
                                userId:
                                    (int) auth()->id(),

                                reason:
                                    $data['reason'],
                            );

                            Notification::make()
                                ->title(
                                    'Customer receipt voided and reversed'
                                )
                                ->success()
                                ->send();
                        } catch (Throwable $exception) {
                            self::sendError($exception);
                        }
                    }),

                EditAction::make()
                    ->visible(
                        fn (
                            CustomerPaymentUpload $record
                        ): bool =>
                            in_array(
                                $record->status,
                                [
                                    'Pending',
                                    'Rejected',
                                ],
                                true
                            )
                            || (
                                $record->status
                                    === 'Approved'
                                && blank(
                                    $record
                                        ->account_voucher_id
                                )
                            )
                    ),
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

    private static function sendError(
        Throwable $exception
    ): void {
        $message = $exception->getMessage();

        if (
            $exception
            instanceof ValidationException
        ) {
            $message = collect(
                $exception->errors()
            )
                ->flatten()
                ->first()
                ?: $message;
        }

        Notification::make()
            ->title('Action failed')
            ->body($message)
            ->danger()
            ->send();
    }
}