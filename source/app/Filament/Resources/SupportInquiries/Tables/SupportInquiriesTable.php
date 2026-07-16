<?php

namespace App\Filament\Resources\SupportInquiries\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SupportInquiriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('inquiry_no')
                    ->label('Inquiry No')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('mobile')
                    ->searchable(),

                TextColumn::make('service_type')
                    ->label('Service')
                    ->badge()
                    ->color('warning')
                    ->searchable(),

                TextColumn::make('subject')
                    ->limit(28)
                    ->searchable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'New' => 'info',
                        'Contacted' => 'warning',
                        'In Progress' => 'primary',
                        'Closed' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y h:i A')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'New' => 'New',
                        'Contacted' => 'Contacted',
                        'In Progress' => 'In Progress',
                        'Closed' => 'Closed',
                    ]),

                SelectFilter::make('service_type')
                    ->label('Service')
                    ->options([
                        'Umrah Package' => 'Umrah Package',
                        'Visit Visa' => 'Visit Visa',
                        'Air Ticketing' => 'Air Ticketing',
                        'Hotel Booking' => 'Hotel Booking',
                        'Travel Insurance' => 'Travel Insurance',
                        'Pakistan Tours' => 'Pakistan Tours',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),

                Action::make('mark_contacted')
                    ->label('Mark Contacted')
                    ->icon('heroicon-o-phone')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function ($record): void {
                        $record->update([
                            'status' => 'Contacted',
                            'contacted_at' => now(),
                        ]);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}