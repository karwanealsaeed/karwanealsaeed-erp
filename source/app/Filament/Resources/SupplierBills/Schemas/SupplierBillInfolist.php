<?php

namespace App\Filament\Resources\SupplierBills\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SupplierBillInfolist
{
    public static function configure(
        Schema $schema
    ): Schema {
        return $schema
            ->components([
                Section::make('Supplier Bill')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('bill_no')
                            ->label('Bill No'),

                        TextEntry::make('supplier.name')
                            ->label('Supplier'),

                        TextEntry::make(
                            'supplier.company_name'
                        )
                            ->label('Company')
                            ->placeholder('-'),

                        TextEntry::make(
                            'supplier_invoice_no'
                        )
                            ->label(
                                'Supplier Invoice No'
                            )
                            ->placeholder('-'),

                        TextEntry::make('reference_no')
                            ->label('Reference No')
                            ->placeholder('-'),

                        TextEntry::make('service_type')
                            ->label('Service')
                            ->badge(),

                        TextEntry::make('bill_date')
                            ->date('d M Y'),

                        TextEntry::make('due_date')
                            ->date('d M Y')
                            ->placeholder('-'),

                        TextEntry::make('days_overdue')
                            ->label('Days Overdue')
                            ->suffix(' Days'),

                        TextEntry::make('sub_total')
                            ->money('PKR'),

                        TextEntry::make('discount')
                            ->money('PKR'),

                        TextEntry::make('tax')
                            ->money('PKR'),

                        TextEntry::make('total_amount')
                            ->money('PKR'),

                        TextEntry::make('paid_amount')
                            ->money('PKR'),

                        TextEntry::make(
                            'balance_amount'
                        )
                            ->money('PKR'),

                        TextEntry::make('status')
                            ->badge(),

                        TextEntry::make('creator.name')
                            ->label('Created By')
                            ->placeholder('-'),

                        TextEntry::make('description')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('notes')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}