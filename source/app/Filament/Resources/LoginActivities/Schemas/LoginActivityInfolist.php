<?php

namespace App\Filament\Resources\LoginActivities\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LoginActivityInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Login Activity')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')->placeholder('-'),
                        TextEntry::make('email')->placeholder('-'),
                        TextEntry::make('ip_address')->label('IP Address')->placeholder('-'),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('logged_in_at')->dateTime('d M Y h:i A')->placeholder('-'),
                        TextEntry::make('logged_out_at')->dateTime('d M Y h:i A')->placeholder('-'),
                        TextEntry::make('user_agent')->columnSpanFull()->placeholder('-'),
                        TextEntry::make('notes')->columnSpanFull()->placeholder('-'),
                    ]),
            ]);
    }
}