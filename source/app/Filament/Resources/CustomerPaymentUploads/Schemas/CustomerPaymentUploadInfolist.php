<?php

namespace App\Filament\Resources\CustomerPaymentUploads\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerPaymentUploadInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Payment Upload')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('upload_no')->label('Upload No'),
                        TextEntry::make('customer.name')->label('Customer'),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('service_type')->label('Service Type')->badge(),
                        TextEntry::make('reference_no')->label('Reference')->placeholder('-'),
                        TextEntry::make('amount')->money('PKR'),
                        TextEntry::make('payment_method')->label('Method')->placeholder('-'),
                        TextEntry::make('payment_date')->date('d M Y')->placeholder('-'),
                        TextEntry::make('transaction_id')->label('Transaction ID')->placeholder('-'),
                        TextEntry::make('approved_at')->dateTime('d M Y h:i A')->placeholder('-'),
                        TextEntry::make('approver.name')->label('Approved By')->placeholder('-'),
                        TextEntry::make('accountVoucher.voucher_no')->label('Accounting Voucher')->placeholder('-'),
                        TextEntry::make('receipt_file')->label('Receipt File')->placeholder('-')->columnSpanFull(),
                        TextEntry::make('customer_notes')->placeholder('-')->columnSpanFull(),
                        TextEntry::make('admin_notes')->placeholder('-')->columnSpanFull(),
                    ]),
            ]);
    }
}