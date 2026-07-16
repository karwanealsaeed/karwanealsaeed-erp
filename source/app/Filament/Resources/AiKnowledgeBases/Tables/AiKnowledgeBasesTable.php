<?php

namespace App\Filament\Resources\AiKnowledgeBases\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AiKnowledgeBasesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('priority', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label('Knowledge Title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category')
                    ->badge()
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('keywords')
                    ->limit(55)
                    ->wrap()
                    ->searchable(),

                TextColumn::make('priority')
                    ->badge()
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Active' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('creator.name')
                    ->label('Created By')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->dateTime('d M Y h:i A')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options([
                        'Umrah' => 'Umrah',
                        'Visa' => 'Visa',
                        'Air Ticketing' => 'Air Ticketing',
                        'Hotel' => 'Hotel',
                        'Agent Ledger' => 'Agent Ledger',
                        'Payment' => 'Payment',
                        'Refund' => 'Refund',
                        'Contact' => 'Contact',
                    ]),

                SelectFilter::make('status')
                    ->options([
                        'Active' => 'Active',
                        'Inactive' => 'Inactive',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}