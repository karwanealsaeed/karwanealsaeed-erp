<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Models\Payment;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('visaApplication.application_no')
                    ->label('Application No')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('visaApplication.customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('payment_date')
                    ->label('Payment Date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('PKR')
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label('Method')
                    ->badge()
                    ->searchable(),

                TextColumn::make('reference_no')
                    ->label('Reference No')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('d M Y h:i A')
                    ->timezone('Asia/Karachi')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Details')
                    ->icon('heroicon-o-eye'),

                Action::make('print')
                    ->label('Print')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->hidden(fn (): bool => ! (auth()->user()?->hasPermission('visa_payments.print') ?? false))
                    ->url(fn (Payment $record): string => route('payment-receipts.print', [
                        'type' => 'visa',
                        'payment' => $record,
                    ]))
                    ->openUrlInNewTab(),

                EditAction::make()
                    ->hidden(fn (): bool => ! (auth()->user()?->hasPermission('visa_payments.edit') ?? false)),

                DeleteAction::make()
                    ->hidden(fn (): bool => ! (auth()->user()?->hasPermission('visa_payments.delete') ?? false))
                    ->after(function (Payment $record): void {
                        $application = $record->visaApplication;

                        if (! $application) {
                            return;
                        }

                        $paidAmount = $application->payments()->sum('amount');

                        $application->update([
                            'paid_amount' => $paidAmount,
                            'balance_amount' => max(((float) $application->total_amount) - ((float) $paidAmount), 0),
                        ]);
                    }),
            ])
            ->defaultSort('id', 'desc');
    }
}
