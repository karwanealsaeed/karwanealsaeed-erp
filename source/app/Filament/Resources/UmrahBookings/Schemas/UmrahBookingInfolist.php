<?php

namespace App\Filament\Resources\UmrahBookings\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UmrahBookingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('booking_no')
                    ->label('Booking No'),

                TextEntry::make('customer.name')
                    ->label('Customer'),

                TextEntry::make('package_name')
                    ->label('Package Name'),

                TextEntry::make('travel_date')
                    ->label('Travel Date')
                    ->date('d M Y'),

                TextEntry::make('return_date')
                    ->label('Return Date')
                    ->date('d M Y')
                    ->placeholder('-'),

                TextEntry::make('makkah_hotel')
                    ->label('Makkah Hotel')
                    ->placeholder('-'),

                TextEntry::make('madinah_hotel')
                    ->label('Madinah Hotel')
                    ->placeholder('-'),

                TextEntry::make('no_of_persons')
                    ->label('No. of Persons'),

                TextEntry::make('package_amount')
                    ->label('Package Amount')
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
