<?php

namespace App\Filament\Resources\ExpenseCategories\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ExpenseCategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextEntry::make('name')
                    ->label('Category Name'),

                TextEntry::make('code')
                    ->label('Category Code')
                    ->placeholder('-'),

                TextEntry::make('description')
                    ->label('Description')
                    ->placeholder('-'),

                IconEntry::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextEntry::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y h:i A'),

                TextEntry::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d M Y h:i A'),

            ]);
    }
}
