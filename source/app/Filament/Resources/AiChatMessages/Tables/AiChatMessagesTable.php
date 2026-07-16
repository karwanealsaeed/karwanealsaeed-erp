<?php

namespace App\Filament\Resources\AiChatMessages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AiChatMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('customer.name')
                    ->label('Agent')
                    ->searchable()
                    ->placeholder('Public Visitor'),

                TextColumn::make('sender')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'assistant' => 'success',
                        default => 'info',
                    }),

                TextColumn::make('language')
                    ->badge()
                    ->sortable(),

                TextColumn::make('category')
                    ->badge()
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('message')
                    ->limit(80)
                    ->wrap()
                    ->searchable(),

                IconColumn::make('needs_human_support')
                    ->label('Human Support')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->dateTime('d M Y h:i A')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('sender')
                    ->options([
                        'user' => 'User',
                        'assistant' => 'Assistant',
                    ]),

                SelectFilter::make('language')
                    ->options([
                        'English' => 'English',
                        'Urdu' => 'Urdu',
                        'Roman Urdu' => 'Roman Urdu',
                    ]),

                TernaryFilter::make('needs_human_support')
                    ->label('Needs Human Support'),
            ])
            ->recordActions([
                ViewAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}