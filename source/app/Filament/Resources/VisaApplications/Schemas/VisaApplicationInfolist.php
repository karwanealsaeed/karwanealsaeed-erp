<?php

namespace App\Filament\Resources\VisaApplications\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class VisaApplicationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextEntry::make('application_no')
                    ->label('Application No'),

                TextEntry::make('customer.name')
                    ->label('Customer'),

                TextEntry::make('country.name')
                    ->label('Country'),

                TextEntry::make('visaType.name')
                    ->label('Visa Type'),

                TextEntry::make('passport_no')
                    ->label('Passport No'),

                TextEntry::make('passport_status')
                    ->label('Passport Status')
                    ->badge(),

                TextEntry::make('passport_received_date')
                    ->label('Passport Received')
                    ->date()
                    ->placeholder('-'),

                TextEntry::make('passport_return_date')
                    ->label('Passport Returned')
                    ->date()
                    ->placeholder('-'),

                TextEntry::make('travel_date')
                    ->label('Travel Date')
                    ->date()
                    ->placeholder('-'),

                TextEntry::make('return_date')
                    ->label('Return Date')
                    ->date()
                    ->placeholder('-'),

                TextEntry::make('visa_fee')
                    ->label('Visa Fee')
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
                    ->label('Notes')
                    ->placeholder('-'),

                TextEntry::make('created_at')
                    ->label('Created At')
                    ->dateTime('d M Y h:i A'),

                TextEntry::make('updated_at')
                    ->label('Updated At')
                    ->dateTime('d M Y h:i A'),

            ]);
    }
}
