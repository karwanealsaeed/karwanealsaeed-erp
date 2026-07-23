<?php

namespace App\Filament\Resources\BankAccounts\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BankAccountsTable
{
    public static function configure(
        Table $table
    ): Table {
        return $table
            ->defaultSort('bank_name')
            ->columns([
                TextColumn::make('bank_name')
                    ->label('Bank')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Account Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('account_number')
                    ->label('Account Number')
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('iban')
                    ->label('IBAN')
                    ->placeholder('-')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('branch_code')
                    ->label('Branch Code')
                    ->placeholder('-')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('swift_code')
                    ->label('SWIFT / BIC')
                    ->placeholder('-')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('accountHead.code')
                    ->label('GL Code')
                    ->badge()
                    ->sortable(),

                TextColumn::make('accountHead.name')
                    ->label('GL Account')
                    ->searchable(),

                TextColumn::make('opening_date')
                    ->label('Opening Date')
                    ->date('d M Y')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('opening_balance')
                    ->label('Opening Balance')
                    ->money('PKR')
                    ->sortable(),

                TextColumn::make(
                    'statement_entries_count'
                )
                    ->label('Statement Entries')
                    ->counts('statementEntries')
                    ->badge(),

                TextColumn::make('status')
                    ->badge()
                    ->color(
                        fn (string $state): string =>
                            $state === 'Active'
                                ? 'success'
                                : 'gray'
                    ),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'Active' => 'Active',
                        'Inactive' => 'Inactive',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}