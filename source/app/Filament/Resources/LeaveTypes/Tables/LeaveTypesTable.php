<?php

namespace App\Filament\Resources\LeaveTypes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LeaveTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('code')
                    ->badge()
                    ->placeholder('-'),

                TextColumn::make('annual_quota')
                    ->label('Annual Quota')
                    ->formatStateUsing(
                        fn ($state): string =>
                            (float) $state > 0
                                ? number_format((float) $state, 2) . ' Days'
                                : 'Unlimited'
                    ),

                IconColumn::make('is_paid')
                    ->label('Paid')
                    ->boolean(),

                TextColumn::make('attachment_required_after_days')
                    ->label('Attachment After')
                    ->formatStateUsing(
                        fn ($state): string =>
                            (int) $state > 0
                                ? $state . ' Days'
                                : 'Optional'
                    ),

                TextColumn::make('staff_leaves_count')
                    ->label('Leave Records')
                    ->counts('staffLeaves')
                    ->badge(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Active' => 'success',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'Active' => 'Active',
                        'Inactive' => 'Inactive',
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