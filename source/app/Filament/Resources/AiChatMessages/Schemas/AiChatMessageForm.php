<?php

namespace App\Filament\Resources\AiChatMessages\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AiChatMessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->relationship('customer', 'name'),
                TextInput::make('sender')
                    ->required(),
                TextInput::make('language')
                    ->required()
                    ->default('Roman Urdu'),
                TextInput::make('category'),
                Textarea::make('message')
                    ->required()
                    ->columnSpanFull(),
                Toggle::make('needs_human_support')
                    ->required(),
                TextInput::make('meta'),
            ]);
    }
}
