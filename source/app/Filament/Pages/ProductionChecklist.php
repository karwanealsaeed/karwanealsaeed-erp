<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ProductionChecklist extends Page
{
    protected string $view = 'filament.pages.production-checklist';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rocket-launch';

    protected static string | \UnitEnum | null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Production Checklist';

    protected static ?int $navigationSort = 22;

    protected static ?string $title = 'Production Checklist';

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function checks(): array
    {
        return [
            [
                'label' => 'Application Environment',
                'value' => config('app.env'),
                'status' => app()->environment('production') ? 'Good' : 'Warning',
                'message' => app()->environment('production')
                    ? 'APP_ENV production par hai.'
                    : 'Live server par APP_ENV=production hona chahiye.',
            ],
            [
                'label' => 'Debug Mode',
                'value' => config('app.debug') ? 'true' : 'false',
                'status' => config('app.debug') ? 'Danger' : 'Good',
                'message' => config('app.debug')
                    ? 'Live server par APP_DEBUG=false zaroori hai.'
                    : 'Debug mode off hai.',
            ],
            [
                'label' => 'Application URL',
                'value' => config('app.url'),
                'status' => filled(config('app.url')) ? 'Good' : 'Warning',
                'message' => 'Live domain APP_URL me set hona chahiye.',
            ],
            [
                'label' => 'Database Connection',
                'value' => $this->databaseStatus(),
                'status' => $this->databaseStatus() === 'Connected' ? 'Good' : 'Danger',
                'message' => 'Database connection working hona zaroori hai.',
            ],
            [
                'label' => 'Storage Link',
                'value' => is_link(public_path('storage')) || File::exists(public_path('storage')) ? 'Available' : 'Missing',
                'status' => is_link(public_path('storage')) || File::exists(public_path('storage')) ? 'Good' : 'Danger',
                'message' => 'Agar missing ho to php artisan storage:link run karein.',
            ],
            [
                'label' => 'Storage Writable',
                'value' => is_writable(storage_path()) ? 'Writable' : 'Not Writable',
                'status' => is_writable(storage_path()) ? 'Good' : 'Danger',
                'message' => 'storage folder writable hona chahiye.',
            ],
            [
                'label' => 'Cache Writable',
                'value' => is_writable(base_path('bootstrap/cache')) ? 'Writable' : 'Not Writable',
                'status' => is_writable(base_path('bootstrap/cache')) ? 'Good' : 'Danger',
                'message' => 'bootstrap/cache folder writable hona chahiye.',
            ],
            [
                'label' => 'Laravel Log File',
                'value' => File::exists(storage_path('logs/laravel.log')) ? 'Available' : 'Missing',
                'status' => File::exists(storage_path('logs/laravel.log')) ? 'Good' : 'Warning',
                'message' => 'Logs monitoring ke liye laravel.log available hona chahiye.',
            ],
            [
                'label' => 'PHP Version',
                'value' => PHP_VERSION,
                'status' => version_compare(PHP_VERSION, '8.3.0', '>=') ? 'Good' : 'Warning',
                'message' => 'Laravel ke liye PHP 8.3+ recommended hai.',
            ],
        ];
    }

    private function databaseStatus(): string
    {
        try {
            DB::connection()->getPdo();

            return 'Connected';
        } catch (\Throwable) {
            return 'Failed';
        }
    }
}