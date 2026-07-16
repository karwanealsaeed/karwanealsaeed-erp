<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PaymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextEntry::make('visaApplication.application_no')
                    ->label('Application No'),

                TextEntry::make('visaApplication.customer.name')
                    ->label('Customer'),

                TextEntry::make('payment_date')
                    ->label('Payment Date')
                    ->date('d M Y'),

                TextEntry::make('amount')
                    ->label('Amount')
                    ->money('PKR'),

                TextEntry::make('payment_method')
                    ->label('Payment Method')
                    ->badge(),

                TextEntry::make('reference_no')
                    ->label('Reference No')
                    ->placeholder('-'),

                TextEntry::make('notes')
                    ->label('Notes')
                    ->placeholder('-'),

                TextEntry::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y h:i A'),

                TextEntry::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d M Y h:i A'),

            ]);
    }
}
