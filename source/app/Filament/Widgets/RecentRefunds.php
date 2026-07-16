<?php

namespace App\Filament\Widgets;

use App\Models\Refund;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentRefunds extends BaseWidget
{
    protected static ?string $heading = 'Recent Refunds';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 11;


    public static function canView(): bool
    {
        return auth()->user()?->hasPermission('refunds.view') ?? false;
    }
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Refund::query()
                    ->with('customer')
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('refund_no')
                    ->label('Refund No')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),

                TextColumn::make('refund_date')
                    ->label('Refund Date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('service_type')
                    ->label('Service')
                    ->badge(),

                TextColumn::make('service_reference')
                    ->label('Reference')
                    ->placeholder('-'),

                TextColumn::make('refund_amount')
                    ->label('Amount')
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
            ])
            ->recordActions([
                Action::make('print_refund')
                    ->label('Print')
                    ->icon('heroicon-o-printer')
                    ->url(fn (Refund $record): string => route('refunds.print', $record))
                    ->openUrlInNewTab(),
            ]);
    }
}
