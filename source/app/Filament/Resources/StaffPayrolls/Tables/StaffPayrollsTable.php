<?php

namespace App\Filament\Resources\StaffPayrolls\Tables;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StaffPayrollsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('payroll_no')
                    ->label('Payroll No')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('staffMember.employee_no')
                    ->label('Employee No')
                    ->searchable(),

                TextColumn::make('staffMember.name')
                    ->label('Staff Member')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('period_text')
                    ->label('Salary Month'),

                TextColumn::make('basic_salary')
                    ->money('PKR')
                    ->sortable(),

                TextColumn::make('net_salary')
                    ->money('PKR')
                    ->sortable(),

                TextColumn::make('paid_amount')
                    ->money('PKR')
                    ->sortable(),

                TextColumn::make('balance_amount')
                    ->money('PKR')
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->placeholder('-'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Paid' => 'success',
                        'Partial' => 'warning',
                        'Unpaid' => 'danger',
                        'Cancelled' => 'gray',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('staff_member_id')
                    ->label('Staff Member')
                    ->relationship('staffMember', 'name')
                    ->searchable(),

                SelectFilter::make('salary_month')
                    ->label('Month')
                    ->options([
                        1 => 'January',
                        2 => 'February',
                        3 => 'March',
                        4 => 'April',
                        5 => 'May',
                        6 => 'June',
                        7 => 'July',
                        8 => 'August',
                        9 => 'September',
                        10 => 'October',
                        11 => 'November',
                        12 => 'December',
                    ]),

                SelectFilter::make('status')
                    ->options([
                        'Unpaid' => 'Unpaid',
                        'Partial' => 'Partial',
                        'Paid' => 'Paid',
                        'Cancelled' => 'Cancelled',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
Action::make('print')
    ->label('Print')
    ->icon('heroicon-o-printer')
    ->color('warning')
    ->url(fn ($record): string => route('staff-payrolls.print', $record))
    ->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}