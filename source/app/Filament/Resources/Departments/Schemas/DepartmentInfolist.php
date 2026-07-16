<?php

namespace App\Filament\Resources\Departments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DepartmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Department')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name'),
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