<?php

namespace App\Filament\Resources\Designations\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DesignationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Designation')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('department.name')
                            ->label('Department')
                            ->placeholder('-'),
                        TextEntry::make('code')->placeholder('-'),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('sort_order'),
                        TextEntry::make('description')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}