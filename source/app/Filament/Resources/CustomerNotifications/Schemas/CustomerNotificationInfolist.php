<?php

namespace App\Filament\Resources\CustomerNotifications\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerNotificationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Notification')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('title')->columnSpan(2),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('customer.name')->label('Customer')->placeholder('All Customers'),
                        TextEntry::make('type')->badge(),
                        TextEntry::make('service_type')->label('Service')->placeholder('-'),
                        TextEntry::make('visible_from')->date('d M Y')->placeholder('-'),
                        TextEntry::make('visible_until')->date('d M Y')->placeholder('-'),
                        IconEntry::make('is_read')->boolean()->label('Read'),
                        TextEntry::make('message')->columnSpanFull(),
                        TextEntry::make('action_label')->placeholder('-'),
                        TextEntry::make('action_url')->placeholder('-')->columnSpan(2),
                    ]),
            ]);
    }
}