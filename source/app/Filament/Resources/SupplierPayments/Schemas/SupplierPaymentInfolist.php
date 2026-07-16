<?php

namespace App\Filament\Resources\SupplierPayments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SupplierPaymentInfolist
{
    public static function configure(
        Schema $schema
    ): Schema {
        return $schema
            ->components([
                Section::make('Supplier Payment')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('payment_no')
                            ->label('Payment No'),

                        TextEntry::make('payment_date')
                            ->label('Payment Date')
                            ->date('d M Y'),

                        TextEntry::make('supplier.name')
                            ->label('Supplier'),

                        TextEntry::make('bill.bill_no')
                            ->label('Bill No'),

                        TextEntry::make(
                            'bill.supplier_invoice_no'
                        )
                            ->label(
                                'Supplier Invoice No'
                            )
                            ->placeholder('-'),

                        TextEntry::make('amount')
                            ->money('PKR'),

                        TextEntry::make(
                            'payment_method'
                        )
                            ->label('Payment Method')
                            ->badge(),

                        TextEntry::make('reference_no')
                            ->label('Reference No')
                            ->placeholder('-'),

                        TextEntry::make('status')
                            ->badge(),

                        TextEntry::make('creator.name')
                            ->label('Created By')
                            ->placeholder('-'),

                        TextEntry::make('approver.name')
                            ->label('Approved By')
                            ->placeholder('-'),

                        TextEntry::make('approved_at')
                            ->label('Approved At')
                            ->dateTime(
                                'd M Y h:i A'
                            )
                            ->placeholder('-'),

                        TextEntry::make('rejecter.name')
                            ->label('Rejected By')
                            ->placeholder('-'),

                        TextEntry::make('rejected_at')
                            ->label('Rejected At')
                            ->dateTime(
                                'd M Y h:i A'
                            )
                            ->placeholder('-'),

                        TextEntry::make(
                            'rejection_reason'
                        )
                            ->label(
                                'Rejection Reason'
                            )
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('notes')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}