<?php

namespace App\Filament\Pages;

use App\Models\SystemErrorIncident;
use App\Services\SystemErrorService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use SplFileObject;
use Throwable;
use UnitEnum;

class ErrorLogViewer extends Page
{
    protected string $view =
        'filament.pages.error-log-viewer';

    protected static string | BackedEnum | null $navigationIcon =
        'heroicon-o-bug-ant';

    protected static string | UnitEnum | null $navigationGroup =
        'Settings';

    protected static ?string $navigationLabel =
        'Error Monitoring';

    protected static ?int $navigationSort =
        21;

    protected static ?string $title =
        'Error Monitoring';

    public string $statusFilter =
        'open';

    public string $severityFilter =
        'all';

    public string $search =
        '';

    public static function canAccess(): bool
    {
        $user =
            auth()->user();

        return $user
            ? (
                $user->isAdmin()
                || $user->hasPermission(
                    'settings.manage',
                )
            )
            : false;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make(
                'run_cleanup',
            )
                ->label(
                    'Run Cleanup',
                )
                ->icon(
                    'heroicon-o-trash',
                )
                ->color(
                    'warning',
                )
                ->requiresConfirmation()
                ->modalDescription(
                    'Old resolved incidents and expired rotated log files will be removed. Open incidents are preserved.',
                )
                ->action(
                    function (): void {
                        Artisan::call(
                            'system:maintain-errors',
                        );

                        Notification::make()
                            ->title(
                                'Error monitoring cleanup completed.',
                            )
                            ->body(
                                trim(
                                    Artisan::output(),
                                ),
                            )
                            ->success()
                            ->send();
                    },
                ),
        ];
    }

    public function summary(): array
    {
        return app(
            SystemErrorService::class,
        )->summary();
    }

    public function incidents(): Collection
    {
        if (
            ! Schema::hasTable(
                'system_error_incidents',
            )
        ) {
            return collect();
        }

        $query =
            SystemErrorIncident::query()
                ->latest(
                    'last_seen_at',
                );

        if (
            $this->statusFilter
            === 'open'
        ) {
            $query->unresolved();
        }

        if (
            $this->statusFilter
            === 'resolved'
        ) {
            $query->resolved();
        }

        if (
            $this->severityFilter
            !== 'all'
        ) {
            $query->severity(
                $this->severityFilter,
            );
        }

        $search =
            trim(
                $this->search,
            );

        if (filled($search)) {
            $query->where(
                function ($query) use (
                    $search,
                ): void {
                    $query
                        ->where(
                            'message',
                            'like',
                            '%'.$search.'%',
                        )
                        ->orWhere(
                            'exception_class',
                            'like',
                            '%'.$search.'%',
                        )
                        ->orWhere(
                            'file',
                            'like',
                            '%'.$search.'%',
                        )
                        ->orWhere(
                            'route_name',
                            'like',
                            '%'.$search.'%',
                        );
                },
            );
        }

        return $query
            ->limit(100)
            ->get();
    }

    public function resolveIncident(
        int $incidentId,
    ): void {
        $incident =
            SystemErrorIncident::query()
                ->findOrFail(
                    $incidentId,
                );

        app(
            SystemErrorService::class,
        )->resolve(
            $incident,
            auth()->user(),
        );

        Notification::make()
            ->title(
                'Incident marked resolved.',
            )
            ->success()
            ->send();
    }

    public function reopenIncident(
        int $incidentId,
    ): void {
        $incident =
            SystemErrorIncident::query()
                ->findOrFail(
                    $incidentId,
                );

        app(
            SystemErrorService::class,
        )->reopen(
            $incident,
        );

        Notification::make()
            ->title(
                'Incident reopened.',
            )
            ->warning()
            ->send();
    }

    public function logFiles(): array
    {
        $files =
            glob(
                storage_path(
                    'logs/*.log*',
                ),
            )
            ?: [];

        $files =
            array_values(
                array_filter(
                    $files,
                    fn (string $file): bool =>
                        is_file($file)
                        && basename($file)
                            !== '.gitignore',
                ),
            );

        usort(
            $files,
            fn (
                string $first,
                string $second,
            ): int =>
                filemtime($second)
                <=> filemtime($first),
        );

        return array_map(
            fn (string $file): array => [
                'name' =>
                    basename($file),

                'size' =>
                    $this->formatBytes(
                        (int) filesize(
                            $file,
                        ),
                    ),

                'modified_at' =>
                    date(
                        'd M Y h:i A',
                        (int) filemtime(
                            $file,
                        ),
                    ),

                'path' =>
                    $file,
            ],
            $files,
        );
    }

    public function rawLog(): array
    {
        $files =
            $this->logFiles();

        $laravelFiles =
            array_values(
                array_filter(
                    $files,
                    fn (array $file): bool =>
                        str_starts_with(
                            $file['name'],
                            'laravel',
                        ),
                ),
            );

        $selected =
            $laravelFiles[0]
            ?? $files[0]
            ?? null;

        if (! $selected) {
            return [
                'name' =>
                    null,

                'content' =>
                    'No log files found.',
            ];
        }

        return [
            'name' =>
                $selected['name'],

            'content' =>
                $this->tailFile(
                    $selected['path'],
                    (int) config(
                        'error_monitoring.raw_log_tail_lines',
                        300,
                    ),
                ),
        ];
    }

    private function tailFile(
        string $path,
        int $lineLimit,
    ): string {
        try {
            if (
                ! is_file($path)
                || filesize($path) === 0
            ) {
                return 'Log file is empty.';
            }

            $file =
                new SplFileObject(
                    $path,
                    'r',
                );

            $file->seek(
                PHP_INT_MAX,
            );

            $lastLine =
                $file->key();

            $startLine =
                max(
                    0,
                    $lastLine
                    - $lineLimit,
                );

            $file->seek(
                $startLine,
            );

            $lines = [];

            while (! $file->eof()) {
                $lines[] =
                    rtrim(
                        $file->fgets(),
                        "\r\n",
                    );
            }

            return implode(
                "\n",
                array_slice(
                    $lines,
                    -$lineLimit,
                ),
            );
        } catch (Throwable $exception) {
            return 'Unable to read log file: '
                .$exception->getMessage();
        }
    }

    private function formatBytes(
        int $bytes,
    ): string {
        if (
            $bytes
            >= 1024 * 1024
        ) {
            return number_format(
                $bytes
                / 1024
                / 1024,
                2,
            )
            .' MB';
        }

        if ($bytes >= 1024) {
            return number_format(
                $bytes / 1024,
                2,
            )
            .' KB';
        }

        return $bytes
            .' Bytes';
    }
}