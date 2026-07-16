<?php

namespace App\Filament\Resources\AccountingPeriodLocks\Tables;

use App\Filament\Resources\AccountingPeriodLocks\AccountingPeriodLockResource;
use App\Models\AccountingPeriodLock;
use App\Services\AccountingPeriodLockService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;
use Throwable;

class AccountingPeriodLocksTable
{
    public static function configure(
        Table $table
    ): Table {
        return $table
            ->defaultSort(
                'period_start',
                'desc'
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Period Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('period_start')
                    ->label('Start')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('period_end')
                    ->label('End')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('lock_scope')
                    ->label('Scope')
                    ->badge()
                    ->searchable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(
                        fn (
                            string $state
                        ): string =>
                            match ($state) {
                                'Locked' =>
                                    'danger',

                                'Unlocked' =>
                                    'warning',

                                default =>
                                    'success',
                            }
                    ),

                TextColumn::make('locker.name')
                    ->label('Locked By')
                    ->placeholder('-'),

                TextColumn::make('locked_at')
                    ->label('Locked At')
                    ->dateTime(
                        'd M Y h:i A'
                    )
                    ->placeholder('-'),

                TextColumn::make('unlocker.name')
                    ->label('Unlocked By')
                    ->placeholder('-')
                    ->toggleable(
                        isToggledHiddenByDefault:
                            true
                    ),

                TextColumn::make('unlocked_at')
                    ->label('Unlocked At')
                    ->dateTime(
                        'd M Y h:i A'
                    )
                    ->placeholder('-')
                    ->toggleable(
                        isToggledHiddenByDefault:
                            true
                    ),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'Open' => 'Open',
                        'Locked' => 'Locked',
                        'Unlocked' => 'Unlocked',
                    ]),

                SelectFilter::make('lock_scope')
                    ->label('Scope')
                    ->options(
                        AccountingPeriodLockService::scopeOptions()
                    ),
            ])
            ->recordActions([
                ViewAction::make(),

                Action::make('lock_period')
                    ->label(
                        fn (
                            AccountingPeriodLock $record
                        ): string =>
                            $record->status
                            === 'Unlocked'
                                ? 'Re-Lock'
                                : 'Lock Period'
                    )
                    ->icon(
                        'heroicon-o-lock-closed'
                    )
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(
                        fn (
                            AccountingPeriodLock $record
                        ): bool =>
                            $record->status
                            !== 'Locked'
                            && AccountingPeriodLockResource::canManagePeriods()
                    )
                    ->action(
                        function (
                            AccountingPeriodLock $record
                        ): void {
                            try {
                                app(
                                    AccountingPeriodLockService::class
                                )->lock(
                                    period: $record,
                                    userId:
                                        (int) auth()->id(),
                                );

                                Notification::make()
                                    ->title(
                                        'Accounting period locked'
                                    )
                                    ->body(
                                        'Selected period mein accounting posting block hogi.'
                                    )
                                    ->success()
                                    ->send();
                            } catch (Throwable $exception) {
                                self::sendError(
                                    $exception
                                );
                            }
                        }
                    ),

                Action::make('unlock_period')
                    ->label('Unlock Period')
                    ->icon(
                        'heroicon-o-lock-open'
                    )
                    ->color('warning')
                    ->schema([
                        Textarea::make('reason')
                            ->label('Unlock Reason')
                            ->required()
                            ->rows(4),
                    ])
                    ->visible(
                        fn (
                            AccountingPeriodLock $record
                        ): bool =>
                            $record->status
                            === 'Locked'
                            && AccountingPeriodLockResource::canManagePeriods()
                    )
                    ->action(
                        function (
                            AccountingPeriodLock $record,
                            array $data
                        ): void {
                            try {
                                app(
                                    AccountingPeriodLockService::class
                                )->unlock(
                                    period: $record,
                                    userId:
                                        (int) auth()->id(),

                                    reason:
                                        $data['reason'],
                                );

                                Notification::make()
                                    ->title(
                                        'Accounting period unlocked'
                                    )
                                    ->warning()
                                    ->send();
                            } catch (Throwable $exception) {
                                self::sendError(
                                    $exception
                                );
                            }
                        }
                    ),

                EditAction::make()
                    ->visible(
                        fn (
                            AccountingPeriodLock $record
                        ): bool =>
                            $record->status
                            !== 'Locked'
                            && AccountingPeriodLockResource::canManagePeriods()
                    ),

                DeleteAction::make()
                    ->visible(
                        fn (
                            AccountingPeriodLock $record
                        ): bool =>
                            $record->status
                            !== 'Locked'
                            && AccountingPeriodLockResource::canManagePeriods()
                    ),
            ]);
    }

    private static function sendError(
        Throwable $exception
    ): void {
        $message =
            $exception->getMessage();

        if (
            $exception
            instanceof ValidationException
        ) {
            $message = collect(
                $exception->errors()
            )
                ->flatten()
                ->first()
                ?: $message;
        }

        Notification::make()
            ->title('Action failed')
            ->body($message)
            ->danger()
            ->send();
    }
}