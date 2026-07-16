<?php

namespace App\Filament\Resources\AccountVouchers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AccountVouchersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('voucher_date', 'desc')
            ->columns([
                TextColumn::make('voucher_no')
                    ->label('Voucher No')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('voucher_date')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('voucher_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Receipt' => 'success',
                        'Payment' => 'danger',
                        'Journal' => 'info',
                        'Contra' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('party_name')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('total_debit')
                    ->money('PKR')
                    ->sortable(),

                TextColumn::make('total_credit')
                    ->money('PKR')
                    ->sortable(),

                IconColumn::make('is_balanced')
                    ->label('Balanced')
                    ->boolean(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Posted' => 'success',
                        'Draft' => 'warning',
                        'Cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('voucher_type')
                    ->options([
                        'Receipt' => 'Receipt',
                        'Payment' => 'Payment',
                        'Journal' => 'Journal',
                        'Contra' => 'Contra',
                    ]),

                SelectFilter::make('status')
                    ->options([
                        'Draft' => 'Draft',
                        'Posted' => 'Posted',
                        'Cancelled' => 'Cancelled',
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