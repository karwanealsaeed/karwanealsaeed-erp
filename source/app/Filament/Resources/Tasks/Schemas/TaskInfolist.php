<?php

namespace App\Filament\Resources\Tasks\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TaskInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Task Details')
                    ->schema([
                        TextEntry::make('title')
                            ->label('Task Title')
                            ->weight('bold')
                            ->columnSpanFull(),

                        TextEntry::make('description')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('task_type')
                            ->badge(),

                        TextEntry::make('status')
                            ->badge()
                            ->color(
                                fn ($record): string =>
                                    $record->status_color,
                            ),

                        TextEntry::make('priority')
                            ->badge()
                            ->color(
                                fn ($record): string =>
                                    $record->priority_color,
                            ),

                        IconEntry::make('is_private')
                            ->label('Private')
                            ->boolean(),
                    ])
                    ->columns(3),

                Section::make('Assignment')
                    ->schema([
                        TextEntry::make('assignedTo.name')
                            ->label('Assigned Staff')
                            ->placeholder('Unassigned'),

                        TextEntry::make('customer.name')
                            ->label('Customer')
                            ->placeholder('-'),

                        TextEntry::make('creator.name')
                            ->label('Created By')
                            ->placeholder('-'),
                    ])
                    ->columns(3),

                Section::make('Schedule')
                    ->schema([
                        TextEntry::make('starts_at')
                            ->dateTime('d M Y h:i A')
                            ->placeholder('-'),

                        TextEntry::make('due_at')
                            ->dateTime('d M Y h:i A')
                            ->placeholder('-'),

                        TextEntry::make('reminder_at')
                            ->dateTime('d M Y h:i A')
                            ->placeholder('-'),

                        TextEntry::make('completed_at')
                            ->dateTime('d M Y h:i A')
                            ->placeholder('-'),

                        TextEntry::make('completedBy.name')
                            ->label('Completed By')
                            ->placeholder('-'),

                        TextEntry::make('completion_notes')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
            ]);
    }
}