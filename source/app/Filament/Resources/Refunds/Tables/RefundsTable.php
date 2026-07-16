<?php

namespace App\Filament\Resources\Refunds\Tables;

use App\Models\Refund;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RefundsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('refund_no')
                    ->label('Refund No')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('refund_date')
                    ->label('Refund Date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('service_type')
                    ->label('Service')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('service_reference')
                    ->label('Service Reference')
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('refund_amount')
                    ->label('Refund Amount')
                    ->money('PKR', divideBy: 1)
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label('Method')
                    ->badge(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pending' => 'warning',
                        'Approved' => 'info',
                        'Paid' => 'success',
                        'Rejected' => 'danger',
                        'Cancelled' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('reference_no')
                    ->label('Reference No')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->recordActions([
                ViewAction::make()->label('Details'),

                EditAction::make(),

                Action::make('print_refund')
                    ->label('Print Refund')
                    ->icon('heroicon-o-printer')
                    ->hidden(fn (): bool => ! (auth()->user()?->hasPermission('refunds.print') ?? false))
                    ->url(fn (Refund $record): string => route('refunds.print', $record))
                    ->openUrlInNewTab(),

                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc');
    }
}

