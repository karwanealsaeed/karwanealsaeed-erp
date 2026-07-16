<?php

namespace App\Filament\Resources\UmrahTransportRoutes\Tables;

use App\Models\UmrahTransportRoute;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UmrahTransportRoutesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('route_code')
                    ->label('Code')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('transport_type')
                    ->label('Type')
                    ->badge()
                    ->placeholder('-'),

                TextColumn::make('vehicle_type')
                    ->label('Vehicle')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('route_description')
                    ->label('Route')
                    ->limit(45)
                    ->placeholder('-'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Active' ? 'success' : 'gray'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Details'),

                EditAction::make()
                    ->hidden(fn (UmrahTransportRoute $record): bool => ! (
                        (auth()->user()?->isAdmin() ?? false)
                        || (auth()->user()?->hasPermission('umrah_bookings.edit') ?? false)
                    )),

                DeleteAction::make()
                    ->hidden(fn (UmrahTransportRoute $record): bool => ! (
                        (auth()->user()?->isAdmin() ?? false)
                        || (auth()->user()?->hasPermission('umrah_bookings.delete') ?? false)
                    )),
            ])
            ->defaultSort('id', 'desc');
    }
}