<?php

namespace App\Filament\Resources\SupplierPayments\Tables;

use App\Models\SupplierPayment;
use App\Services\SupplierAccountingPostingService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;
use Throwable;

class SupplierPaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('payment_date', 'desc')
            ->columns([
                TextColumn::make('payment_no')
                    ->label('Payment No')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('payment_date')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable(),

                TextColumn::make('bill.bill_no')
                    ->label('Bill No')
                    ->badge()
                    ->searchable(),

                TextColumn::make('amount')
                    ->money('PKR')
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label('Method')
                    ->badge(),

                TextColumn::make(
                    'paymentAccountHead.code'
                )
                    ->label('Payment GL')
                    ->badge()
                    ->placeholder('-'),

                TextColumn::make('reference_no')
                    ->label('Reference')
                    ->placeholder('-')
                    ->searchable(),

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

                TextColumn::make(
                    'accountVoucher.voucher_no'
                )
                    ->label('GL Voucher')
                    ->badge()
                    ->placeholder('Not Posted'),

                TextColumn::make('approver.name')
                    ->label('Approved By')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('posted_at')
                    ->label('Posted At')
                    ->dateTime('d M Y h:i A')
                    ->placeholder('-')
                    ->toggleable(
                        isToggledHiddenByDefault: true
                    ),
            ])
            ->filters([
                SelectFilter::make('supplier_id')
                    ->label('Supplier')
                    ->relationship(
                        'supplier',
                        'name'
                    )
                    ->searchable()
                    ->preload(),

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
                        'Credit Card' => 'Credit Card',
                        'Adjustment' => 'Adjustment',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),

                Action::make('approve')
                    ->label('Approve & Post')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription(
                        'Approval par Supplier Payable Debit aur Cash/Bank Credit voucher post hoga.'
                    )
                    ->visible(
                        fn (SupplierPayment $record): bool =>
                            $record->status === 'Pending'
                    )
                    ->action(function (
                        SupplierPayment $record
                    ): void {
                        try {
                            app(
                                SupplierAccountingPostingService::class
                            )->approvePayment(
                                $record,
                                (int) auth()->id()
                            );

                            Notification::make()
                                ->title(
                                    'Supplier payment approved and posted'
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
                            ->label('Rejection Reason')
                            ->required()
                            ->rows(4),
                    ])
                    ->visible(
                        fn (SupplierPayment $record): bool =>
                            $record->status === 'Pending'
                    )
                    ->action(function (
                        SupplierPayment $record,
                        array $data
                    ): void {
                        try {
                            app(
                                SupplierAccountingPostingService::class
                            )->rejectPayment(
                                $record,
                                (int) auth()->id(),
                                $data['reason']
                            );

                            Notification::make()
                                ->title(
                                    'Supplier payment rejected'
                                )
                                ->danger()
                                ->send();
                        } catch (Throwable $exception) {
                            self::sendError($exception);
                        }
                    }),

                Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-no-symbol')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(
                        fn (SupplierPayment $record): bool =>
                            $record->status === 'Pending'
                    )
                    ->action(function (
                        SupplierPayment $record
                    ): void {
                        try {
                            app(
                                SupplierAccountingPostingService::class
                            )->cancelPayment($record);

                            Notification::make()
                                ->title(
                                    'Supplier payment cancelled'
                                )
                                ->success()
                                ->send();
                        } catch (Throwable $exception) {
                            self::sendError($exception);
                        }
                    }),

                EditAction::make()
                    ->visible(
                        fn (SupplierPayment $record): bool =>
                            $record->status === 'Pending'
                    ),
            ]);
    }

    private static function sendError(
        Throwable $exception
    ): void {
        $message = $exception->getMessage();

        if ($exception instanceof ValidationException) {
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