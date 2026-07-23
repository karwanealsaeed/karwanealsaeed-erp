<?php

namespace App\Filament\Resources\Agents\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AgentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('customer_code')
                    ->label('Agent Code'),

                TextEntry::make('agency_name')
                    ->label('Agency Name')
                    ->placeholder('-'),

                TextEntry::make('name')
                    ->label('Contact Person'),

                TextEntry::make('mobile'),

                TextEntry::make('whatsapp')
                    ->placeholder('-'),

                TextEntry::make('email')
                    ->placeholder('-'),

                TextEntry::make('cnic')
                    ->label('CNIC')
                    ->placeholder('-'),

                TextEntry::make('city')
                    ->placeholder('-'),

                TextEntry::make('country'),

                TextEntry::make('commission_rate')
                    ->label('Commission Rate')
                    ->formatStateUsing(
                        fn ($state): string =>
                            filled($state)
                                ? number_format((float) $state, 2).'%'
                                : '-',
                    ),

                TextEntry::make('credit_limit')
                    ->label('Credit Limit')
                    ->formatStateUsing(
                        fn ($state): string =>
                            filled($state)
                                ? 'PKR '.number_format((float) $state, 2)
                                : '-',
                    ),

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