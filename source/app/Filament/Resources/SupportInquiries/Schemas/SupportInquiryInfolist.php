<?php

namespace App\Filament\Resources\SupportInquiries\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SupportInquiryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('inquiry_no')->label('Inquiry No'),
                        TextEntry::make('name'),
                        TextEntry::make('mobile'),
                        TextEntry::make('email'),
                        TextEntry::make('service_type')->label('Service Type')->badge(),
                        TextEntry::make('status')->badge(),
                    ]),

                Section::make('Message')
                    ->schema([
                        TextEntry::make('subject')->placeholder('-'),
                        TextEntry::make('message')->columnSpanFull(),
                        TextEntry::make('smart_reply')->label('Smart Reply')->columnSpanFull(),
                        TextEntry::make('admin_notes')->label('Admin Notes')->placeholder('-')->columnSpanFull(),
                    ]),
            ]);
    }
}