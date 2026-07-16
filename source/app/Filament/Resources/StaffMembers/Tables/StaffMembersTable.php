<?php

namespace App\Filament\Resources\StaffMembers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StaffMembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('employee_no')
                    ->label('Employee No')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('designation')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('department')
                    ->badge()
                    ->color('info')
                    ->placeholder('-'),

                TextColumn::make('mobile')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('joining_date')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('basic_salary')
                    ->money('PKR')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Active' => 'success',
                        'Inactive' => 'warning',
                        'Left' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'Active' => 'Active',
                        'Inactive' => 'Inactive',
                        'Left' => 'Left',
                    ]),

                SelectFilter::make('department')
                    ->options([
                        'Admin' => 'Admin',
                        'Sales' => 'Sales',
                        'Accounts' => 'Accounts',
                        'Operations' => 'Operations',
                        'Visa' => 'Visa',
                        'Ticketing' => 'Ticketing',
                        'Umrah' => 'Umrah',
                        'Marketing' => 'Marketing',
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