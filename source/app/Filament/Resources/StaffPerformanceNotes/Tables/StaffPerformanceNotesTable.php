<?php

namespace App\Filament\Resources\StaffPerformanceNotes\Tables;

use App\Models\StaffPerformanceNote;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StaffPerformanceNotesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('review_date', 'desc')
            ->columns([
                TextColumn::make('note_no')
                    ->label('Note No')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('staff.name')
                    ->label('Staff Member')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('staff.department.name')
                    ->label('Department')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('review_type')
                    ->label('Review Type')
                    ->badge()
                    ->searchable(),

                TextColumn::make('title')
                    ->limit(45)
                    ->searchable(),

                TextColumn::make('review_date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('rating')
                    ->label('Rating')
                    ->formatStateUsing(
                        fn ($state): string =>
                            filled($state)
                                ? number_format((float) $state, 1) . ' / 5'
                                : '-'
                    )
                    ->badge()
                    ->color(fn ($state): string => match (true) {
                        (float) $state >= 4 => 'success',
                        (float) $state >= 3 => 'info',
                        (float) $state >= 2 => 'warning',
                        filled($state) => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('reviewer.name')
                    ->label('Reviewed By')
                    ->placeholder('-'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Final' => 'success',
                        default => 'warning',
                    }),
            ])
            ->filters([
                SelectFilter::make('staff_id')
                    ->label('Staff')
                    ->relationship('staff', 'name'),

                SelectFilter::make('review_type')
                    ->options([
                        'Performance Review' => 'Performance Review',
                        'Appreciation' => 'Appreciation',
                        'Warning' => 'Warning',
                        'Probation Review' => 'Probation Review',
                        'Training Review' => 'Training Review',
                        'Promotion Review' => 'Promotion Review',
                        'General Note' => 'General Note',
                    ]),

                SelectFilter::make('status')
                    ->options([
                        'Draft' => 'Draft',
                        'Final' => 'Final',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),

                Action::make('finalize')
                    ->label('Finalize')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(
                        fn (StaffPerformanceNote $record): bool =>
                            $record->status === 'Draft'
                    )
                    ->action(function (
                        StaffPerformanceNote $record
                    ): void {
                        $record->update([
                            'status' => 'Final',
                            'reviewed_by' => auth()->id(),
                        ]);

                        Notification::make()
                            ->title('Performance note finalized')
                            ->success()
                            ->send();
                    }),

                EditAction::make()
                    ->visible(
                        fn (StaffPerformanceNote $record): bool =>
                            $record->status === 'Draft'
                    ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}