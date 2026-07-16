<?php

namespace App\Filament\Resources\StaffPerformanceNotes\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StaffPerformanceNoteInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Performance Review')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('note_no')
                            ->label('Note No'),

                        TextEntry::make('staff.name')
                            ->label('Staff Member'),

                        TextEntry::make('review_type')
                            ->badge(),

                        TextEntry::make('title')
                            ->columnSpan(2),

                        TextEntry::make('rating')
                            ->formatStateUsing(
                                fn ($state): string =>
                                    filled($state)
                                        ? number_format((float) $state, 1) . ' / 5'
                                        : '-'
                            ),

                        TextEntry::make('review_date')
                            ->date('d M Y'),

                        TextEntry::make('next_review_date')
                            ->date('d M Y')
                            ->placeholder('-'),

                        TextEntry::make('status')
                            ->badge(),

                        TextEntry::make('reviewer.name')
                            ->label('Reviewed By')
                            ->placeholder('-'),

                        TextEntry::make('strengths')
                            ->label('Strengths / Achievements')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('improvements')
                            ->label('Areas for Improvement')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('goals')
                            ->label('Future Goals / Targets')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('notes')
                            ->label('Additional Notes')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('attachment')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}