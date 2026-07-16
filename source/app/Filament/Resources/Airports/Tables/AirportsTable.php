<?php

namespace App\Filament\Resources\Airports\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AirportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('country.name')
                    ->label('Country')
                    ->searchable(),

                TextColumn::make('name')
                    ->searchable(),

                TextColumn::make('city'),

                TextColumn::make('iata_code'),

                TextColumn::make('icao_code'),

                IconColumn::make('status')
                    ->boolean(),

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
