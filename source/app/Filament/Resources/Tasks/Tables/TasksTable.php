<?php

namespace App\Filament\Resources\Tasks\Tables;

use App\Models\Task;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TasksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Task')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->description(
                        fn (Task $record): ?string =>
                            $record->customer?->name,
                    ),

                TextColumn::make('task_type')
                    ->label('Type')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('assignedTo.name')
                    ->label('Assigned To')
                    ->placeholder('Unassigned')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(
                        fn (Task $record): string =>
                            $record->status_color,
                    )
                    ->sortable(),

                TextColumn::make('priority')
                    ->badge()
                    ->color(
                        fn (Task $record): string =>
                            $record->priority_color,
                    )
                    ->sortable(),

                TextColumn::make('due_at')
                    ->label('Due')
                    ->dateTime('d M Y h:i A')
                    ->placeholder('No due date')
                    ->sortable()
                    ->color(
                        fn (Task $record): ?string =>
                            $record->is_overdue
                                ? 'danger'
                                : (
                                    $record->is_due_today
                                        ? 'warning'
                                        : null
                                ),
                    )
                    ->weight(
                        fn (Task $record): ?string =>
                            $record->is_overdue
                            || $record->is_due_today
                                ? 'bold'
                                : null,
                    ),

                IconColumn::make('is_private')
                    ->label('Private')
                    ->boolean()
                    ->toggleable(
                        isToggledHiddenByDefault: true,
                    ),

                TextColumn::make('created_at')
                    ->dateTime('d M Y h:i A')
                    ->sortable()
                    ->toggleable(
                        isToggledHiddenByDefault: true,
                    ),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(Task::statusOptions())
                    ->multiple(),

                SelectFilter::make('priority')
                    ->options(Task::priorityOptions())
                    ->multiple(),

                SelectFilter::make('task_type')
                    ->label('Task Type')
                    ->options(Task::typeOptions())
                    ->multiple(),

                SelectFilter::make('assigned_to_id')
                    ->label('Assigned Staff')
                    ->relationship(
                        'assignedTo',
                        'name',
                    )
                    ->searchable()
                    ->preload(),

                SelectFilter::make('customer_id')
                    ->label('Customer')
                    ->relationship(
                        'customer',
                        'name',
                    )
                    ->searchable()
                    ->preload(),

                Filter::make('overdue')
                    ->label('Overdue Tasks')
                    ->query(
                        fn (Builder $query): Builder =>
                            $query->overdue(),
                    ),

                Filter::make('due_today')
                    ->label('Due Today')
                    ->query(
                        fn (Builder $query): Builder =>
                            $query->dueToday(),
                    ),

                Filter::make('my_tasks')
                    ->label('My Tasks')
                    ->query(
                        fn (Builder $query): Builder =>
                            $query->where(
                                'assigned_to_id',
                                auth()->id(),
                            ),
                    ),
            ])
            ->recordActions([
                Action::make('start')
                    ->label('Start')
                    ->icon('heroicon-o-play')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(
                        fn (Task $record): bool =>
                            $record->status
                            === Task::STATUS_PENDING,
                    )
                    ->action(
                        fn (Task $record): bool =>
                            $record->update([
                                'status' =>
                                    Task::STATUS_IN_PROGRESS,
                            ]),
                    )
                    ->successNotificationTitle(
                        'Task marked as in progress.',
                    ),

                Action::make('complete')
                    ->label('Complete')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(
                        fn (Task $record): bool =>
                            ! in_array(
                                $record->status,
                                [
                                    Task::STATUS_COMPLETED,
                                    Task::STATUS_CANCELLED,
                                ],
                                true,
                            ),
                    )
                    ->action(
                        fn (Task $record): bool =>
                            $record->update([
                                'status' =>
                                    Task::STATUS_COMPLETED,
                            ]),
                    )
                    ->successNotificationTitle(
                        'Task completed successfully.',
                    ),

                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('complete')
                        ->label('Mark Completed')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(
                            function (
                                Collection $records,
                            ): void {
                                $records->each(
                                    function (Task $task): void {
                                        if (
                                            in_array(
                                                $task->status,
                                                [
                                                    Task::STATUS_COMPLETED,
                                                    Task::STATUS_CANCELLED,
                                                ],
                                                true,
                                            )
                                        ) {
                                            return;
                                        }

                                        $task->update([
                                            'status' =>
                                                Task::STATUS_COMPLETED,
                                        ]);
                                    },
                                );
                            },
                        )
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('due_at');
    }
}