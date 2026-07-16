<?php

namespace App\Filament\Resources\FlightBookings\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class FlightBookingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('booking_no')
                    ->label('Booking No'),

                TextEntry::make('customer.name')
                    ->label('Customer'),

                TextEntry::make('airline.name')
                    ->label('Airline'),

                TextEntry::make('fromAirport.name')
                    ->label('From Airport'),

                TextEntry::make('toAirport.name')
                    ->label('To Airport'),

                TextEntry::make('pnr')
                    ->label('PNR')
                    ->placeholder('-'),

                TextEntry::make('ticket_no')
                    ->label('Ticket No')
                    ->placeholder('-'),

                TextEntry::make('departure_date')
                    ->label('Departure Date')
                    ->date('d M Y'),

                TextEntry::make('return_date')
                    ->label('Return Date')
                    ->date('d M Y')
                    ->placeholder('-'),

                TextEntry::make('trip_type')
                    ->label('Trip Type')
                    ->badge(),

                TextEntry::make('class')
                    ->badge(),

                TextEntry::make('fare')
                    ->money('PKR', divideBy: 1),

                TextEntry::make('tax')
                    ->money('PKR', divideBy: 1),

                TextEntry::make('service_fee')
                    ->label('Service Fee')
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
                    ->badge(),

                TextEntry::make('notes')
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
