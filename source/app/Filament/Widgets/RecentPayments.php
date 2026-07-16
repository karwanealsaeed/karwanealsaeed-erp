<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentPayments extends TableWidget
{
    protected static ?string $heading = 'Recent Payments';

    protected int|string|array $columnSpan = 'full';


    public static function canView(): bool
    {
        return auth()->user()?->hasPermission('visa_payments.view') ?? false;
    }
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Payment::query()->latest()->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('visaApplication.application_no')
                    ->label('Application No')
                    ->searchable(),

                Tables\Columns\TextColumn::make('visaApplication.customer.name')
                    ->label('Customer')
                    ->searchable(),

                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Payment Date')
                    ->date(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('PKR', divideBy: 1),

                Tables\Columns\BadgeColumn::make('payment_method')
                    ->label('Method'),

                Tables\Columns\TextColumn::make('reference_no')
                    ->label('Reference')
                    ->placeholder('-'),
            ])
            ->paginated(false);
    }
}
