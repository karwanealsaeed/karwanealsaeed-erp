<?php

namespace App\Filament\Resources\StaffLeaves\Tables;

use App\Models\StaffLeave;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StaffLeavesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('leave_no')
                    ->label('Leave No')
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

                TextColumn::make('leaveType.name')
                    ->label('Leave Type')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('start_date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('total_days')
                    ->label('Days')
                    ->suffix(' Days')
                    ->sortable(),

                TextColumn::make('session')
                    ->badge()
                    ->toggleable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Approved' => 'success',
                        'Rejected' => 'danger',
                        'Cancelled' => 'gray',
                        default => 'warning',
                    })
                    ->sortable(),

                TextColumn::make('approved_at')
                    ->label('Approved At')
                    ->dateTime('d M Y h:i A')
                    ->placeholder('-')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('staff_id')
                    ->label('Staff')
                    ->relationship('staff', 'name'),

                SelectFilter::make('leave_type_id')
                    ->label('Leave Type')
                    ->relationship('leaveType', 'name'),

                SelectFilter::make('status')
                    ->options([
                        'Pending' => 'Pending',
                        'Approved' => 'Approved',
                        'Rejected' => 'Rejected',
                        'Cancelled' => 'Cancelled',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),

                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(
                        fn (StaffLeave $record): bool =>
                            $record->status === 'Pending'
                    )
                    ->action(function (StaffLeave $record): void {
                        $record->loadMissing('leaveType');

                        if (! $record->canBeApproved()) {
                            $remaining = $record->remainingQuota();

                            Notification::make()
                                ->title('Leave quota exceeded')
                                ->body(
                                    'Available balance: '
                                    . number_format((float) $remaining, 2)
                                    . ' days.'
                                )
                                ->danger()
                                ->send();

                            return;
                        }

                        $record->update([
                            'status' => 'Approved',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                            'rejected_by' => null,
                            'rejected_at' => null,
                            'rejection_reason' => null,
                        ]);

                        Notification::make()
                            ->title('Leave approved')
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->schema([
                        Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->rows(4),
                    ])
                    ->visible(
                        fn (StaffLeave $record): bool =>
                            $record->status === 'Pending'
                    )
                    ->action(function (
                        StaffLeave $record,
                        array $data
                    ): void {
                        $record->update([
                            'status' => 'Rejected',
                            'rejected_by' => auth()->id(),
                            'rejected_at' => now(),
                            'rejection_reason' =>
                                $data['rejection_reason'],
                            'approved_by' => null,
                            'approved_at' => null,
                        ]);

                        Notification::make()
                            ->title('Leave rejected')
                            ->danger()
                            ->send();
                    }),

                Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-no-symbol')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(
                        fn (StaffLeave $record): bool =>
                            in_array(
                                $record->status,
                                ['Pending', 'Approved'],
                                true
                            )
                    )
                    ->action(function (StaffLeave $record): void {
                        $record->update([
                            'status' => 'Cancelled',
                            'approved_by' => null,
                            'approved_at' => null,
                        ]);
                    }),

                EditAction::make()
                    ->visible(
                        fn (StaffLeave $record): bool =>
                            $record->status === 'Pending'
                    ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}