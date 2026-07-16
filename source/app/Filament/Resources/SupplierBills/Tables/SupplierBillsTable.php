<?php

namespace App\Filament\Resources\SupplierBills\Tables;

use App\Models\SupplierBill;
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

class SupplierBillsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('bill_date', 'desc')
            ->columns([
                TextColumn::make('bill_no')
                    ->label('Bill No')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('service_type')
                    ->label('Service')
                    ->badge(),

                TextColumn::make('bill_date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('expenseAccountHead.code')
                    ->label('Expense GL')
                    ->badge()
                    ->placeholder('-'),

                TextColumn::make('payableAccountHead.code')
                    ->label('Payable GL')
                    ->badge()
                    ->placeholder('-'),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('PKR')
                    ->sortable(),

                TextColumn::make('paid_amount')
                    ->label('Paid')
                    ->money('PKR'),

                TextColumn::make('balance_amount')
                    ->label('Balance')
                    ->money('PKR')
                    ->color(
                        fn ($state): string =>
                            (float) $state > 0
                                ? 'danger'
                                : 'success'
                    ),

                TextColumn::make('approval_status')
                    ->label('Approval')
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

                TextColumn::make('status')
                    ->label('Payment Status')
                    ->badge()
                    ->color(
                        fn (string $state): string =>
                            match ($state) {
                                'Paid' => 'success',
                                'Partially Paid' => 'info',
                                'Unpaid' => 'warning',
                                'Cancelled' => 'danger',
                                default => 'gray',
                            }
                    ),

                TextColumn::make('accountVoucher.voucher_no')
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
                SelectFilter::make('approval_status')
                    ->label('Approval')
                    ->options([
                        'Pending' => 'Pending',
                        'Approved' => 'Approved',
                        'Rejected' => 'Rejected',
                        'Cancelled' => 'Cancelled',
                    ]),

                SelectFilter::make('status')
                    ->label('Payment Status')
                    ->options([
                        'Draft' => 'Draft',
                        'Unpaid' => 'Unpaid',
                        'Partially Paid' =>
                            'Partially Paid',
                        'Paid' => 'Paid',
                        'Cancelled' => 'Cancelled',
                    ]),

                SelectFilter::make('service_type')
                    ->label('Service')
                    ->options([
                        'Airline' => 'Airline / Ticketing',
                        'Hotel' => 'Hotel',
                        'Visa' => 'Visa',
                        'Transport' => 'Transport',
                        'Umrah' => 'Umrah',
                        'Insurance' => 'Insurance',
                        'Tour' => 'Tour',
                        'Other' => 'Other',
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
                        'Bill approve hote hi Expense Debit aur Supplier Payable Credit voucher post hoga.'
                    )
                    ->visible(
                        fn (SupplierBill $record): bool =>
                            $record->approval_status
                            === 'Pending'
                    )
                    ->action(function (
                        SupplierBill $record
                    ): void {
                        try {
                            app(
                                SupplierAccountingPostingService::class
                            )->approveBill(
                                $record,
                                (int) auth()->id()
                            );

                            Notification::make()
                                ->title(
                                    'Supplier bill approved and posted'
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
                        fn (SupplierBill $record): bool =>
                            $record->approval_status
                            === 'Pending'
                    )
                    ->action(function (
                        SupplierBill $record,
                        array $data
                    ): void {
                        try {
                            app(
                                SupplierAccountingPostingService::class
                            )->rejectBill(
                                $record,
                                (int) auth()->id(),
                                $data['reason']
                            );

                            Notification::make()
                                ->title(
                                    'Supplier bill rejected'
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
                        fn (SupplierBill $record): bool =>
                            $record->approval_status
                            === 'Rejected'
                    )
                    ->action(function (
                        SupplierBill $record
                    ): void {
                        try {
                            app(
                                SupplierAccountingPostingService::class
                            )->resubmitBill($record);

                            Notification::make()
                                ->title(
                                    'Supplier bill resubmitted'
                                )
                                ->success()
                                ->send();
                        } catch (Throwable $exception) {
                            self::sendError($exception);
                        }
                    }),

                Action::make('cancel_bill')
                    ->label('Cancel Bill')
                    ->icon('heroicon-o-no-symbol')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalDescription(
                        'Posted voucher cancel hoga. Payment wale bill ko cancel nahi kiya ja sakta.'
                    )
                    ->visible(
                        fn (SupplierBill $record): bool =>
                            $record->approval_status
                                === 'Approved'
                            && (float) $record->paid_amount
                                <= 0
                    )
                    ->action(function (
                        SupplierBill $record
                    ): void {
                        try {
                            app(
                                SupplierAccountingPostingService::class
                            )->cancelBill(
                                $record,
                                (int) auth()->id()
                            );

                            Notification::make()
                                ->title(
                                    'Supplier bill cancelled'
                                )
                                ->success()
                                ->send();
                        } catch (Throwable $exception) {
                            self::sendError($exception);
                        }
                    }),

                EditAction::make()
                    ->visible(
                        fn (SupplierBill $record): bool =>
                            in_array(
                                $record->approval_status,
                                ['Pending', 'Rejected'],
                                true
                            )
                            && (float) $record->paid_amount
                                <= 0
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