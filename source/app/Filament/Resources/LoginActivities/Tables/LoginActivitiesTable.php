<?php

namespace App\Filament\Resources\LoginActivities\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LoginActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Logout' ? 'success' : 'info'),

                TextColumn::make('logged_in_at')
                    ->label('Login Time')
                    ->dateTime('d M Y h:i A')
                    ->sortable(),

                TextColumn::make('logged_out_at')
                    ->label('Logout Time')
                    ->dateTime('d M Y h:i A')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('user_agent')
                    ->label('Device / Browser')
                    ->limit(40)
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'Login' => 'Login',
                        'Logout' => 'Logout',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}