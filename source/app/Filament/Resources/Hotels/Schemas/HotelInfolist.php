<?php

namespace App\Filament\Resources\Hotels\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class HotelInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('hotel_code')
                    ->label('Hotel Code'),

                TextEntry::make('hotel_name')
                    ->label('Hotel Name'),

                TextEntry::make('city.name')
                    ->label('City'),

                TextEntry::make('city.country.name')
                    ->label('Country'),

                TextEntry::make('supplier.display_name')
                    ->label('Supplier')
                    ->placeholder('-'),

                TextEntry::make('star_rating')
                    ->label('Star Rating')
                    ->formatStateUsing(
                        fn ($state): string =>
                            filled($state)
                                ? ((int) $state).' Star'
                                : '-',
                    ),

                TextEntry::make('contact_no')
                    ->label('Contact No')
                    ->placeholder('-'),

                TextEntry::make('email')
                    ->placeholder('-'),

                TextEntry::make('website')
                    ->placeholder('-'),

                TextEntry::make('status')
                    ->badge(),

                TextEntry::make('address')
                    ->placeholder('-'),

                TextEntry::make('notes')
                    ->placeholder('-'),

                TextEntry::make('created_at')
                    ->dateTime('d M Y, h:i A'),

                TextEntry::make('updated_at')
                    ->dateTime('d M Y, h:i A'),
            ]);
    }
}