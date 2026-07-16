<?php

namespace App\Filament\Resources\Invoices\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class InvoiceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('invoice_no')
                    ->label('Invoice No'),

                TextEntry::make('customer.name')
                    ->label('Customer'),

                TextEntry::make('invoice_date')
                    ->label('Invoice Date')
                    ->date('d M Y'),

                TextEntry::make('due_date')
                    ->label('Due Date')
                    ->date('d M Y')
                    ->placeholder('-'),

                TextEntry::make('service_type')
                    ->label('Service Type')
                    ->badge(),

                TextEntry::make('service_reference')
                    ->label('Service Reference')
                    ->placeholder('-'),

                TextEntry::make('sub_total')
                    ->label('Sub Total')
                    ->money('PKR', divideBy: 1),

                TextEntry::make('discount')
                    ->label('Discount')
                    ->money('PKR', divideBy: 1),

                TextEntry::make('tax')
                    ->label('Tax')
                    ->money('PKR', divideBy: 1),

                TextEntry::make('total_amount')
                    ->label('Total Amount')
                    ->money('PKR', divideBy: 1),

                TextEntry::make('paid_amount')
                    ->label('Paid Amount')
                    ->money('PKR', divideBy: 1),

                TextEntry::make('balance_amount')
                    ->label('Balance Amount')
                    ->money('PKR', divideBy: 1),

                TextEntry::make('status')
                    ->label('Status')
                    ->badge(),

                TextEntry::make('notes')
                    ->label('Notes')
                    ->placeholder('-')
                    ->columnSpanFull(),

                TextEntry::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y h:i A'),

                TextEntry::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d M Y h:i A'),
            ]);
    }
}
