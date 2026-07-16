<?php

namespace App\Filament\Resources\UmrahPayments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UmrahPaymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('umrahBooking.booking_no')
                    ->label('Booking No'),

                TextEntry::make('umrahBooking.customer.name')
                    ->label('Customer'),

                TextEntry::make('umrahBooking.package_name')
                    ->label('Package'),

                TextEntry::make('payment_date')
                    ->label('Payment Date')
                    ->date('d M Y'),

                TextEntry::make('amount')
                    ->label('Amount')
                    ->money('PKR', divideBy: 1),

                TextEntry::make('payment_method')
                    ->label('Payment Method')
                    ->badge(),

                TextEntry::make('reference_no')
                    ->label('Reference No')
                    ->placeholder('-'),

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
