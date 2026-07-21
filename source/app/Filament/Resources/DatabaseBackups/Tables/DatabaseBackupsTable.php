<?php

namespace App\Filament\Resources\DatabaseBackups\Tables;

use App\Models\DatabaseBackup;
use App\Services\DatabaseBackupService;
use App\Services\DatabaseRestoreService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DatabaseBackupsTable
{
    public static function configure(
        Table $table,
    ): Table {
        return $table
            ->columns([
                TextColumn::make(
                    'backup_no',
                )
                    ->label('Backup No')
                    ->searchable()
                    ->sortable(),

                TextColumn::make(
                    'backup_type',
                )
                    ->label('Type')
                    ->badge()
                    ->color(
                        fn (
                            ?string $state,
                        ): string =>
                            $state === 'Scheduled'
                                ? 'info'
                                : 'gray',
                    ),

                TextColumn::make(
                    'database_name',
                )
                    ->label('Database')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make(
                    'file_name',
                )
                    ->label('File Name')
                    ->searchable()
                    ->wrap(),

                TextColumn::make(
                    'formatted_file_size',
                )
                    ->label('Size'),

                TextColumn::make(
                    'verification_status',
                )
                    ->label('Integrity')
                    ->badge()
                    ->color(
                        fn (
                            ?string $state,
                        ): string =>
                            match ($state) {
                                'Verified' =>
                                    'success',

                                'Pending' =>
                                    'warning',

                                'Corrupt',
                                'Failed' =>
                                    'danger',

                                default =>
                                    'gray',
                            },
                    ),

                TextColumn::make(
                    'verified_at',
                )
                    ->label('Verified At')
                    ->dateTime(
                        'd M Y h:i A',
                    )
                    ->placeholder('-')
                    ->toggleable(
                        isToggledHiddenByDefault:
                            true,
                    ),

                TextColumn::make(
                    'checksum_sha256',
                )
                    ->label('SHA-256')
                    ->limit(20)
                    ->placeholder('-')
                    ->toggleable(
                        isToggledHiddenByDefault:
                            true,
                    ),

                TextColumn::make(
                    'verification_error',
                )
                    ->label('Integrity Error')
                    ->limit(80)
                    ->wrap()
                    ->placeholder('-')
                    ->toggleable(
                        isToggledHiddenByDefault:
                            true,
                    ),

                TextColumn::make(
                    'drive_status',
                )
                    ->label('Google Drive')
                    ->badge()
                    ->color(
                        fn (
                            ?string $state,
                        ): string =>
                            match ($state) {
                                'Uploaded' =>
                                    'success',

                                'Pending' =>
                                    'warning',

                                'Failed' =>
                                    'danger',

                                'Disabled',
                                'Not Uploaded' =>
                                    'gray',

                                default =>
                                    'gray',
                            },
                    ),

                TextColumn::make(
                    'drive_uploaded_at',
                )
                    ->label('Drive Uploaded At')
                    ->dateTime(
                        'd M Y h:i A',
                    )
                    ->placeholder('-')
                    ->toggleable(
                        isToggledHiddenByDefault:
                            true,
                    ),

                TextColumn::make(
                    'drive_error',
                )
                    ->label('Drive Error')
                    ->limit(60)
                    ->wrap()
                    ->placeholder('-')
                    ->toggleable(
                        isToggledHiddenByDefault:
                            true,
                    ),

                TextColumn::make(
                    'status',
                )
                    ->label('Status')
                    ->badge()
                    ->color(
                        fn (
                            string $state,
                        ): string =>
                            match ($state) {
                                'Completed' =>
                                    'success',

                                'Processing' =>
                                    'warning',

                                'Failed' =>
                                    'danger',

                                default =>
                                    'gray',
                            },
                    ),

                TextColumn::make(
                    'user.name',
                )
                    ->label('Created By')
                    ->placeholder('System')
                    ->searchable(),

                TextColumn::make(
                    'created_at',
                )
                    ->label('Created At')
                    ->dateTime(
                        'd M Y h:i A',
                    )
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Details')
                    ->icon(
                        'heroicon-o-eye',
                    ),

                Action::make('verify')
                    ->label('Verify')
                    ->icon(
                        'heroicon-o-shield-check',
                    )
                    ->color('warning')
                    ->hidden(
                        fn (): bool =>
                            ! (
                                auth()->user()
                                    ?->hasPermission(
                                        'database_backups.view',
                                    )
                                ?? false
                            ),
                    )
                    ->action(
                        function (
                            DatabaseBackup $record,
                        ): void {
                            $verified =
                                app(
                                    DatabaseBackupService::class,
                                )->verify($record);

                            $record->refresh();

                            $notification =
                                Notification::make()
                                    ->title(
                                        $verified
                                            ? 'Backup integrity verified'
                                            : 'Backup integrity failed',
                                    );

                            if ($verified) {
                                $notification
                                    ->body(
                                        'SHA-256: '
                                        .$record
                                            ->checksum_sha256,
                                    )
                                    ->success();
                            } else {
                                $notification
                                    ->body(
                                        $record
                                            ->verification_error
                                        ?: 'Verification failed.',
                                    )
                                    ->danger();
                            }

                            $notification->send();
                        },
                    ),

                Action::make('open_drive')
                    ->label('Open Drive')
                    ->icon(
                        'heroicon-o-link',
                    )
                    ->color('info')
                    ->hidden(
                        fn (
                            DatabaseBackup $record,
                        ): bool =>
                            blank(
                                $record
                                    ->drive_web_view_link,
                            ),
                    )
                    ->url(
                        fn (
                            DatabaseBackup $record,
                        ): string =>
                            $record
                                ->drive_web_view_link,
                    )
                    ->openUrlInNewTab(),

                Action::make('restore_readiness')
                    ->label('Restore Check')
                    ->icon('heroicon-o-shield-check')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Check Restore Readiness')
                    ->modalDescription(
                        'Backup integrity, database target, SQL safety and MySQL client check honge. Database mein koi change nahi hoga.',
                    )
                    ->hidden(
                        fn (): bool =>
                            ! (
                                auth()->user()
                                    ?->hasPermission(
                                        'database_backups.restore',
                                    )
                                ?? false
                            ),
                    )
                    ->action(
                        function (
                            DatabaseBackup $record,
                        ): void {
                            try {
                                $plan =
                                    app(
                                        DatabaseRestoreService::class,
                                    )->inspect(
                                        $record,
                                    );

                                Notification::make()
                                    ->title(
                                        'Backup restore ke liye ready hai',
                                    )
                                    ->body(
                                        $plan['backup_no']
                                        .' | '
                                        .$plan['database_name']
                                        .' | '
                                        .$plan['formatted_file_size']
                                        .' | Actual execution abhi locked hai.',
                                    )
                                    ->success()
                                    ->send();
                            } catch (\Throwable $exception) {
                                report(
                                    $exception,
                                );

                                Notification::make()
                                    ->title(
                                        'Restore readiness check failed',
                                    )
                                    ->body(
                                        $exception
                                            ->getMessage(),
                                    )
                                    ->danger()
                                    ->send();
                            }
                        },
                    ),

                Action::make('download')
                    ->label('Download')
                    ->icon(
                        'heroicon-o-arrow-down-tray',
                    )
                    ->color('success')
                    ->hidden(
                        fn (): bool =>
                            ! (
                                auth()->user()
                                    ?->hasPermission(
                                        'database_backups.download',
                                    )
                                ?? false
                            ),
                    )
                    ->url(
                        fn (
                            DatabaseBackup $record,
                        ): string =>
                            route(
                                'database-backups.download',
                                $record,
                            ),
                    )
                    ->openUrlInNewTab(),

                DeleteAction::make()
                    ->hidden(
                        fn (): bool =>
                            ! (
                                auth()->user()
                                    ?->hasPermission(
                                        'database_backups.delete',
                                    )
                                ?? false
                            ),
                    ),
            ])
            ->defaultSort(
                'id',
                'desc',
            );
    }
}