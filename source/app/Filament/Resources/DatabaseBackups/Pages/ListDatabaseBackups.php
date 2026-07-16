<?php

namespace App\Filament\Resources\DatabaseBackups\Pages;

use App\Filament\Resources\DatabaseBackups\DatabaseBackupResource;
use App\Services\DatabaseBackupService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Throwable;

class ListDatabaseBackups extends ListRecords
{
    protected static string $resource = DatabaseBackupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_backup')
                ->label('Create Backup')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Create Database Backup')
                ->modalDescription('Kya aap current database ka backup create karna chahte hain?')
                ->hidden(fn (): bool => ! (auth()->user()?->hasPermission('database_backups.create') ?? false))
                ->action(function (): void {
                    try {
                        app(DatabaseBackupService::class)->create();

                        Notification::make()
                            ->title('Backup created successfully')
                            ->success()
                            ->send();
                    } catch (Throwable $exception) {
                        report($exception);

                        Notification::make()
                            ->title('Backup failed')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
