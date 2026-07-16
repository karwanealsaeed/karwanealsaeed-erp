<?php

namespace App\Filament\Resources\StaffDocuments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StaffDocumentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Staff Document')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('document_no')
                            ->label('Record No'),

                        TextEntry::make('staff.name')
                            ->label('Staff Member'),

                        TextEntry::make('document_type')
                            ->badge(),

                        TextEntry::make('title')
                            ->columnSpan(2),

                        TextEntry::make('document_number')
                            ->label('Document Number')
                            ->placeholder('-'),

                        TextEntry::make('issue_date')
                            ->date('d M Y')
                            ->placeholder('-'),

                        TextEntry::make('expiry_date')
                            ->date('d M Y')
                            ->placeholder('No Expiry'),

                        TextEntry::make('status')
                            ->badge(),

                        TextEntry::make('issuing_authority')
                            ->label('Issuing Authority')
                            ->placeholder('-'),

                        TextEntry::make('uploader.name')
                            ->label('Uploaded By')
                            ->placeholder('-'),

                        TextEntry::make('file_path')
                            ->label('File')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('notes')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}