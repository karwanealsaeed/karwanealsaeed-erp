<?php

namespace App\Filament\Resources\LeaveTypes\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LeaveTypeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Leave Type')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('code')->placeholder('-'),
                        TextEntry::make('status')->badge(),

                        TextEntry::make('annual_quota')
                            ->suffix(' Days'),

                        IconEntry::make('is_paid')
                            ->label('Paid Leave')
                            ->boolean(),

                        TextEntry::make('attachment_required_after_days')
                            ->label('Attachment Required After')
                            ->suffix(' Days'),

                        TextEntry::make('description')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}