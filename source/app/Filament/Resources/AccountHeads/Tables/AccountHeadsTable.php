<?php

namespace App\Filament\Resources\AccountHeads\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AccountHeadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('code')
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Account Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Asset' => 'success',
                        'Liability' => 'danger',
                        'Equity' => 'info',
                        'Income' => 'warning',
                        'Expense' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('sub_type')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('opening_debit')
                    ->money('PKR')
                    ->sortable(),

                TextColumn::make('opening_credit')
                    ->money('PKR')
                    ->sortable(),

                TextColumn::make('balance')
                    ->label('Current Balance')
                    ->money('PKR'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Active' ? 'success' : 'gray'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'Asset' => 'Asset',
                        'Liability' => 'Liability',
                        'Equity' => 'Equity',
                        'Income' => 'Income',
                        'Expense' => 'Expense',
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