<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentInvoices extends TableWidget
{
    protected static ?string $heading = 'Recent Invoices';

    protected int|string|array $columnSpan = 'full';


    public static function canView(): bool
    {
        return auth()->user()?->hasPermission('invoices.view') ?? false;
    }
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Invoice::query()->latest()->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('invoice_no')
                    ->label('Invoice No')
                    ->searchable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),

                Tables\Columns\TextColumn::make('invoice_date')
                    ->label('Invoice Date')
                    ->date('d M Y'),

                Tables\Columns\TextColumn::make('service_type')
                    ->label('Service')
                    ->badge(),

                Tables\Columns\TextColumn::make('service_reference')
                    ->label('Reference')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('PKR', divideBy: 1),

                Tables\Columns\TextColumn::make('balance_amount')
                    ->label('Balance')
                    ->money('PKR', divideBy: 1),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
            ])
            ->paginated(false);
    }
}
