<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ErrorLogViewer extends Page
{
    protected string $view = 'filament.pages.error-log-viewer';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-bug-ant';

    protected static string | \UnitEnum | null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Error Logs';

    protected static ?int $navigationSort = 21;

    protected static ?string $title = 'Error Logs';

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('clear_logs')
                ->label('Clear Logs')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function (): void {
                    $path = storage_path('logs/laravel.log');

                    if (file_exists($path)) {
                        file_put_contents($path, '');
                    }

                    Notification::make()
                        ->title('Error logs cleared successfully.')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function logs(): string
    {
        $path = storage_path('logs/laravel.log');

        if (! file_exists($path)) {
            return 'No laravel.log file found.';
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES);

        if (! is_array($lines) || count($lines) === 0) {
            return 'Log file is empty.';
        }

        return implode("\n", array_slice($lines, -400));
    }
}