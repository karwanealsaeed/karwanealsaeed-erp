<?php

namespace App\Filament\Resources\Cities\Schemas;

use Filament\Schemas\Schema;

class CityInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('code')
                    ->label('City Code'),
                TextEntry::make('name'),
                TextEntry::make('country.name')
                    ->label('Country'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
