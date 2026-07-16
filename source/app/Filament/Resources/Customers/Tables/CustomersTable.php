<?php

namespace App\Filament\Resources\Customers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('mobile')
                    ->searchable(),

                TextColumn::make('cnic')
                    ->searchable(),

                TextColumn::make('passport_no')
                    ->label('Passport'),

                TextColumn::make('city')
                    ->searchable(),

                TextColumn::make('customer_type')
                    ->badge(),

                IconColumn::make('status')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->dateTime('d M Y'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()->label('Details')->icon('heroicon-o-eye'),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
