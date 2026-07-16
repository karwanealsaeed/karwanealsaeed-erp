<?php

namespace App\Filament\Resources\FlightPayments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class FlightPaymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('flightBooking.booking_no')
                    ->label('Booking No'),

                TextEntry::make('flightBooking.customer.name')
                    ->label('Customer'),

                TextEntry::make('flightBooking.airline.name')
                    ->label('Airline'),

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
