<?php

namespace App\Filament\Resources\CustomerFamilyMembers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerFamilyMemberInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Family Member')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('customer.name')->label('Customer'),
                        TextEntry::make('name'),
                        TextEntry::make('relationship')->badge()->placeholder('-'),
                        TextEntry::make('cnic')->label('CNIC')->placeholder('-'),
                        TextEntry::make('passport_no')->label('Passport')->placeholder('-'),
                        TextEntry::make('date_of_birth')->date('d M Y')->placeholder('-'),
                        TextEntry::make('mobile')->placeholder('-'),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('notes')->placeholder('-')->columnSpanFull(),
                    ]),
            ]);
    }
}