<?php

namespace App\Filament\Resources\Refunds\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RefundInfolist
{
    public static function configure(
        Schema $schema
    ): Schema {
        return $schema
            ->components([
                Section::make('Customer Refund')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('refund_no')
                            ->label('Refund No'),

                        TextEntry::make('customer.name')
                            ->label('Customer / Agent')
                            ->placeholder('-'),

                        TextEntry::make('refund_type')
                            ->label('Refund Type')
                            ->badge()
                            ->placeholder('-'),

                        TextEntry::make('invoice.invoice_no')
                            ->label('Invoice')
                            ->placeholder('-'),

                        TextEntry::make(
                            'originalReceipt.upload_no'
                        )
                            ->label('Original Receipt')
                            ->placeholder('-'),

                        TextEntry::make('refund_date')
                            ->label('Refund Date')
                            ->date('d M Y')
                            ->placeholder('-'),

                        TextEntry::make('refund_amount')
                            ->label('Refund Amount')
                            ->money('PKR'),

                        TextEntry::make('service_type')
                            ->label('Service')
                            ->placeholder('-'),

                        TextEntry::make(
                            'service_reference'
                        )
                            ->label('Service Reference')
                            ->placeholder('-'),

                        TextEntry::make('reason')
                            ->label('Refund Reason')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('status')
                            ->badge()
                            ->color(
                                fn (?string $state): string =>
                                    match ($state) {
                                        'Approved' => 'success',
                                        'Rejected' => 'danger',
                                        'Cancelled' => 'gray',
                                        default => 'warning',
                                    }
                            ),

                        TextEntry::make('posting_status')
                            ->label('GL Posting')
                            ->badge()
                            ->color(
                                fn (?string $state): string =>
                                    match ($state) {
                                        'Posted' => 'success',
                                        'Missing GL' => 'danger',
                                        'Voided' => 'gray',
                                        default => 'warning',
                                    }
                            ),
                    ]),

                Section::make('Accounting Information')
                    ->columns(3)
                    ->schema([
                        TextEntry::make(
                            'refundAccountHead.code'
                        )
                            ->label('Payment GL Code')
                            ->badge()
                            ->placeholder('-'),

                        TextEntry::make(
                            'refundAccountHead.name'
                        )
                            ->label(
                                'Cash / Bank / Wallet'
                            )
                            ->placeholder('-'),

                        TextEntry::make(
                            'receivableAccountHead.code'
                        )
                            ->label('A/R GL Code')
                            ->badge()
                            ->placeholder('-'),

                        TextEntry::make(
                            'receivableAccountHead.name'
                        )
                            ->label(
                                'Accounts Receivable'
                            )
                            ->placeholder('-'),

                        TextEntry::make(
                            'advanceAccountHead.code'
                        )
                            ->label('Advance GL Code')
                            ->badge()
                            ->placeholder('-'),

                        TextEntry::make(
                            'advanceAccountHead.name'
                        )
                            ->label(
                                'Customer Advance Account'
                            )
                            ->placeholder('-'),

                        TextEntry::make(
                            'accountVoucher.voucher_no'
                        )
                            ->label('GL Voucher')
                            ->badge()
                            ->placeholder('Not Posted'),

                        TextEntry::make(
                            'invoice_balance_reversed'
                        )
                            ->label(
                                'Invoice Balance Reversed'
                            )
                            ->formatStateUsing(
                                fn ($state): string =>
                                    $state ? 'Yes' : 'No'
                            )
                            ->badge(),

                        TextEntry::make('approver.name')
                            ->label('Approved By')
                            ->placeholder('-'),

                        TextEntry::make('approved_at')
                            ->label('Approved At')
                            ->dateTime('d M Y h:i A')
                            ->placeholder('-'),

                        TextEntry::make('poster.name')
                            ->label('Posted By')
                            ->placeholder('-'),

                        TextEntry::make('posted_at')
                            ->label('Posted At')
                            ->dateTime('d M Y h:i A')
                            ->placeholder('-'),

                        TextEntry::make('rejecter.name')
                            ->label('Rejected By')
                            ->placeholder('-'),

                        TextEntry::make('rejected_at')
                            ->label('Rejected At')
                            ->dateTime('d M Y h:i A')
                            ->placeholder('-'),

                        TextEntry::make(
                            'rejection_reason'
                        )
                            ->label('Rejection Reason')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('voider.name')
                            ->label('Voided By')
                            ->placeholder('-'),

                        TextEntry::make('voided_at')
                            ->label('Voided At')
                            ->dateTime('d M Y h:i A')
                            ->placeholder('-'),

                        TextEntry::make('void_reason')
                            ->label('Void Reason')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('notes')
                            ->label('Notes')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}