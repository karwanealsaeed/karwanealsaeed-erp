<?php

namespace App\Filament\Resources\HotelBookings\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class HotelBookingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('booking_no')
                    ->label('Booking No'),

                TextEntry::make('customer.name')
                    ->label('Customer'),

                TextEntry::make('hotel.hotel_code')
                    ->label('Hotel Code')
                    ->placeholder('-'),

                TextEntry::make('country.name')
                    ->label('Country'),

                TextEntry::make('city')
                    ->label('City'),

                TextEntry::make('hotel_name')
                    ->label('Hotel Name'),

                TextEntry::make('check_in_date')
                    ->label('Check-in Date')
                    ->date('d M Y'),

                TextEntry::make('check_out_date')
                    ->label('Check-out Date')
                    ->date('d M Y')
                    ->placeholder('-'),

                TextEntry::make('no_of_rooms')
                    ->label('No. of Rooms'),

                TextEntry::make('no_of_persons')
                    ->label('No. of Persons'),

                TextEntry::make('room_type')
                    ->label('Room Type')
                    ->placeholder('-'),

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
