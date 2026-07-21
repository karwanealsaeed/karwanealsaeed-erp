<?php

namespace App\Filament\Resources\DatabaseBackups\Tables;

use App\Models\DatabaseBackup;
use App\Services\DatabaseBackupService;
use App\Services\DatabaseRestoreService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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
                                        .(
                                            $plan['execution_enabled']
                                                ? ' | Restore execution enabled hai.'
                                                : ' | Actual execution abhi locked hai.'
                                        ),
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

                Action::make('restore_database')
                    ->label('Restore Database')
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalIcon(
                        'heroicon-o-exclamation-triangle',
                    )
                    ->modalHeading(
                        fn (
                            DatabaseBackup $record,
                        ): string =>
                            'Restore Database from '
                            .$record->backup_no,
                    )
                    ->modalDescription(
                        fn (
                            DatabaseBackup $record,
                        ): string =>
                            'Current database overwrite hogi. Pehle verified safety backup create hogi. '
                            .'Confirmation ke liye '
                            .$record->backup_no
                            .' type karein aur current account password enter karein.',
                    )
                    ->modalSubmitActionLabel(
                        'Create Safety Backup and Restore',
                    )
                    ->closeModalByClickingAway(
                        false,
                    )
                    ->closeModalByEscaping(
                        false,
                    )
                    ->disabled(
                        fn (): bool =>
                            ! (
                                (bool) config(
                                    'database_restore.execution_enabled',
                                    false,
                                )
                            ),
                    )
                    ->tooltip(
                        fn (): string =>
                            config(
                                'database_restore.execution_enabled',
                                false,
                            )
                                ? 'Verified safety backup create karke database restore karein.'
                                : 'Restore execution environment configuration se locked hai.',
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
                    ->schema(
                        fn (
                            DatabaseBackup $record,
                        ): array => [
                            TextInput::make(
                                'confirmation',
                            )
                                ->label(
                                    'Type Backup Number',
                                )
                                ->helperText(
                                    'Bilkul isi tarah type karein: '
                                    .$record->backup_no,
                                )
                                ->placeholder(
                                    $record->backup_no,
                                )
                                ->required()
                                ->maxLength(
                                    50,
                                ),

                            TextInput::make(
                                'password',
                            )
                                ->label(
                                    'Current Account Password',
                                )
                                ->password()
                                ->revealable()
                                ->required()
                                ->maxLength(
                                    255,
                                ),

                            Checkbox::make(
                                'acknowledge',
                            )
                                ->label(
                                    'Main samajhta hoon ke current database overwrite hogi aur operation ke duran application maintenance mode mein jayegi.',
                                )
                                ->accepted()
                                ->required(),
                        ],
                    )
                    ->action(
                        function (
                            DatabaseBackup $record,
                            array $data,
                        ): void {
                            try {
                                $user =
                                    auth()->user();

                                if (
                                    ! $user
                                    || ! $user
                                        ->hasPermission(
                                            'database_backups.restore',
                                        )
                                ) {
                                    throw new RuntimeException(
                                        'Database restore permission required hai.',
                                    );
                                }

                                if (
                                    trim(
                                        (string) (
                                            $data['confirmation']
                                            ?? ''
                                        ),
                                    )
                                    !== (string) $record
                                        ->backup_no
                                ) {
                                    throw ValidationException::withMessages([
                                        'confirmation' =>
                                            'Exact backup number type karein: '
                                            .$record->backup_no,
                                    ]);
                                }

                                if (
                                    ! (
                                        $data['acknowledge']
                                        ?? false
                                    )
                                ) {
                                    throw ValidationException::withMessages([
                                        'acknowledge' =>
                                            'Database overwrite acknowledgement required hai.',
                                    ]);
                                }

                                if (
                                    ! Hash::check(
                                        (string) (
                                            $data['password']
                                            ?? ''
                                        ),
                                        (string) $user
                                            ->password,
                                    )
                                ) {
                                    throw ValidationException::withMessages([
                                        'password' =>
                                            'Current account password incorrect hai.',
                                    ]);
                                }

                                $result =
                                    app(
                                        DatabaseRestoreService::class,
                                    )->execute(
                                        backup: $record,
                                        confirmation: (string) $data[
                                            'confirmation'
                                        ],
                                        requestedBy: $user->id,
                                    );

                                Notification::make()
                                    ->title(
                                        'Database restored successfully',
                                    )
                                    ->body(
                                        'Source: '
                                        .$result['source_backup_no']
                                        .' | Safety backup: '
                                        .$result['safety_backup_no']
                                        .' | Restore log: #'
                                        .$result['restore_log_id'],
                                    )
                                    ->success()
                                    ->persistent()
                                    ->send();
                            } catch (ValidationException $exception) {
                                throw $exception;
                            } catch (\Throwable $exception) {
                                report(
                                    $exception,
                                );

                                Notification::make()
                                    ->title(
                                        'Database restore failed',
                                    )
                                    ->body(
                                        $exception
                                            ->getMessage(),
                                    )
                                    ->danger()
                                    ->persistent()
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