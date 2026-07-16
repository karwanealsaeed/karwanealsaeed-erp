<?php

namespace App\Filament\Resources\Invoices\Tables;

use App\Models\Invoice;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_no')
                    ->label('Invoice No')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('invoice_date')
                    ->label('Invoice Date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('service_type')
                    ->label('Service')
                    ->badge()
                    ->sortable(),

                TextColumn::make('service_reference')
                    ->label('Reference')
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('PKR', divideBy: 1)
                    ->sortable(),

                TextColumn::make('paid_amount')
                    ->label('Paid')
                    ->money('PKR', divideBy: 1)
                    ->sortable(),

                TextColumn::make('balance_amount')
                    ->label('Balance')
                    ->money('PKR', divideBy: 1)
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Draft' => 'gray',
                        'Unpaid' => 'danger',
                        'Partially Paid' => 'warning',
                        'Paid' => 'success',
                        'Cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ViewAction::make()->label('Details'),

                EditAction::make(),

                Action::make('print')
                    ->label('Print')
                    ->icon('heroicon-o-printer')
                    ->hidden(fn (): bool => ! (auth()->user()?->hasPermission('invoices.print') ?? false))
                    ->url(fn (Invoice $record): string => route('invoices.print', $record))
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
