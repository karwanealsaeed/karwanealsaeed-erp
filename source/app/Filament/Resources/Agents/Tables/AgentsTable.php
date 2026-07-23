<?php

namespace App\Filament\Resources\Agents\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AgentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer_code')
                    ->label('Agent Code')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('agency_name')
                    ->label('Agency')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Contact Person')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('mobile')
                    ->searchable(),

                TextColumn::make('city')
                    ->searchable(),

                TextColumn::make('commission_rate')
                    ->label('Commission')
                    ->formatStateUsing(
                        fn ($state): string =>
                            filled($state)
                                ? number_format((float) $state, 2).'%'
                                : '-',
                    )
                    ->sortable(),

                TextColumn::make('credit_limit')
                    ->label('Credit Limit')
                    ->money('PKR')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([
                ViewAction::make()
                    ->label('Details')
                    ->icon('heroicon-o-eye'),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}