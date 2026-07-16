<?php

namespace App\Filament\Resources\StaffAttendances\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StaffAttendancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('attendance_date', 'desc')
            ->columns([
                TextColumn::make('staffMember.employee_no')
                    ->label('Employee No')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('staffMember.name')
                    ->label('Staff Member')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('attendance_date')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Present' => 'success',
                        'Absent' => 'danger',
                        'Leave' => 'warning',
                        'Half Day' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('check_in')
                    ->label('Check In')
                    ->placeholder('-'),

                TextColumn::make('check_out')
                    ->label('Check Out')
                    ->placeholder('-'),

                TextColumn::make('working_hours')
                    ->label('Hours')
                    ->numeric(2)
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('staff_member_id')
                    ->label('Staff Member')
                    ->relationship('staffMember', 'name')
                    ->searchable(),

                SelectFilter::make('status')
                    ->options([
                        'Present' => 'Present',
                        'Absent' => 'Absent',
                        'Leave' => 'Leave',
                        'Half Day' => 'Half Day',
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