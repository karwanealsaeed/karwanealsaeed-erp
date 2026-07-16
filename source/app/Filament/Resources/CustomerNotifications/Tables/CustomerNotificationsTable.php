<?php

namespace App\Filament\Resources\CustomerNotifications\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CustomerNotificationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('title')->searchable()->sortable()->limit(35),
                TextColumn::make('customer.name')->label('Customer')->placeholder('All Customers')->searchable(),
                TextColumn::make('type')->badge()->color('warning')->sortable(),
                TextColumn::make('service_type')->label('Service')->placeholder('-')->badge(),
                TextColumn::make('visible_from')->date('d M Y')->placeholder('-'),
                TextColumn::make('visible_until')->date('d M Y')->placeholder('-'),
                IconColumn::make('is_read')->boolean()->label('Read'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Active' ? 'success' : 'gray'),
                TextColumn::make('created_at')->dateTime('d M Y h:i A')->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'Notification' => 'Notification',
                        'Offer' => 'Offer',
                        'Promotion' => 'Promotion',
                        'Alert' => 'Alert',
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