<?php

namespace App\Filament\Resources\Staff\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StaffTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                ImageColumn::make('photo')
                    ->label('Photo')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(
                        'https://ui-avatars.com/api/?name=Staff'
                    ),

                TextColumn::make('staff_code')
                    ->label('Staff Code')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('name')
                    ->label('Staff Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('department.name')
                    ->label('Department')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('designation.name')
                    ->label('Designation')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('mobile')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('joining_date')
                    ->label('Joining Date')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('basic_salary')
                    ->label('Basic Salary')
                    ->money('PKR')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Active' => 'success',
                        'Inactive' => 'gray',
                        'Resigned' => 'warning',
                        'Terminated' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime('d M Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('department_id')
                    ->label('Department')
                    ->relationship('department', 'name'),

                SelectFilter::make('designation_id')
                    ->label('Designation')
                    ->relationship('designation', 'name'),

                SelectFilter::make('employment_type')
                    ->label('Employment Type')
                    ->options([
                        'Permanent' => 'Permanent',
                        'Contract' => 'Contract',
                        'Part Time' => 'Part Time',
                        'Internship' => 'Internship',
                        'Daily Wages' => 'Daily Wages',
                    ]),

                SelectFilter::make('status')
                    ->options([
                        'Active' => 'Active',
                        'Inactive' => 'Inactive',
                        'Resigned' => 'Resigned',
                        'Terminated' => 'Terminated',
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