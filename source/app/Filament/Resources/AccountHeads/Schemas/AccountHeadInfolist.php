<?php

namespace App\Filament\Resources\AccountHeads\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AccountHeadInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account Details')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('code'),
                        TextEntry::make('name'),
                        TextEntry::make('type')->badge(),
                        TextEntry::make('sub_type')->badge(),
                        TextEntry::make('opening_debit')->money('PKR'),
                        TextEntry::make('opening_credit')->money('PKR'),
                        TextEntry::make('balance')->money('PKR'),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('notes')->columnSpanFull()->placeholder('-'),
                    ]),
            ]);
    }
}