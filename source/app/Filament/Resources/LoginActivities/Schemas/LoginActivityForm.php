<?php

namespace App\Filament\Resources\LoginActivities\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LoginActivityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Login Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->disabled(),
                        TextInput::make('email')->disabled(),
                        TextInput::make('ip_address')->disabled(),
                        TextInput::make('status')->disabled(),
                        DateTimePicker::make('logged_in_at')->disabled(),
                        DateTimePicker::make('logged_out_at')->disabled(),
                        Textarea::make('user_agent')->columnSpanFull()->disabled(),
                        Textarea::make('notes')->columnSpanFull()->disabled(),
                    ]),
            ]);
    }
}