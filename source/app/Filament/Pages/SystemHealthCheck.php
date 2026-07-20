<?php

namespace App\Filament\Pages;

use App\Models\SystemErrorIncident;
use App\Services\SchedulerHealthService;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;
use UnitEnum;

class SystemHealthCheck extends Page
{
    protected static string | \BackedEnum | null $navigationIcon =
        'heroicon-o-shield-check';

    protected static string | UnitEnum | null $navigationGroup =
        'Settings';

    protected static ?int $navigationSort =
        5;

    protected static ?string $navigationLabel =
        'System Health';

    protected string $view =
        'filament.pages.system-health-check';

    public static function canAccess(): bool
    {
        return auth()->user()
            ?->hasPermission(
                'settings.manage',
            )
            ?? false;
    }

    public function getChecks(): array
    {
        $schedulerHealth =
            app(
                SchedulerHealthService::class,
            );

        $schedulerHealth
            ->alertIfStale();

        return [
            'Scheduler & Queue' =>
                $this->schedulerQueueChecks(
                    $schedulerHealth,
                ),

            'Error Monitoring' =>
                $this->errorMonitoringChecks(),

            'Database' =>
                $this->databaseChecks(),

            'Storage' =>
                $this->storageChecks(),

            'Google Drive Backup' =>
                $this->googleDriveChecks(),

            'Routes' =>
                $this->routeChecks(),

            'Core Tables' =>
                $this->tableChecks(),
        ];
    }

    private function schedulerQueueChecks(
        SchedulerHealthService $healthService,
    ): array {
        $scheduler =
            $healthService->status();

        $queue =
            $healthService
                ->queueStatus();

        return [
            [
                'name' =>
                    'Laravel Scheduler Heartbeat',

                'status' =>
                    $scheduler['healthy'],

                'message' =>
                    $scheduler['message'],
            ],
            [
                'name' =>
                    'Queue Connection',

                'status' =>
                    filled(
                        $queue['connection'],
                    ),

                'message' =>
                    'Active connection: '
                    .$queue['connection']
                    .'.',
            ],
            [
                'name' =>
                    'Queue Database Tables',

                'status' =>
                    $queue['tables_ready'],

                'message' =>
                    $queue['tables_ready']
                        ? 'jobs and failed_jobs tables are available.'
                        : 'Queue database tables are missing.',
            ],
            [
                'name' =>
                    'Pending Queue Jobs',

                'status' =>
                    $queue['pending_healthy'],

                'message' =>
                    $queue['pending_jobs']
                    .' pending job(s). Limit: '
                    .$queue[
                        'maximum_pending_jobs'
                    ]
                    .'.',
            ],
            [
                'name' =>
                    'Failed Queue Jobs',

                'status' =>
                    $queue['failed_healthy'],

                'message' =>
                    $queue['failed_jobs']
                    .' failed job(s).',
            ],
        ];
    }

    private function errorMonitoringChecks(): array
    {
        $tableReady =
            Schema::hasTable(
                'system_error_incidents',
            );

        $unresolved =
            $tableReady
                ? SystemErrorIncident::query()
                    ->unresolved()
                    ->count()
                : 0;

        $critical =
            $tableReady
                ? SystemErrorIncident::query()
                    ->unresolved()
                    ->severity(
                        'critical',
                    )
                    ->count()
                : 0;

        $stackChannels =
            (array) config(
                'logging.channels.stack.channels',
                [],
            );

        $dailyLogging =
            config(
                'logging.default',
            )
            === 'daily'
            || in_array(
                'daily',
                $stackChannels,
                true,
            );

        $debugSafe =
            ! app()->environment(
                'production',
            )
            || ! config(
                'app.debug',
            );

        return [
            [
                'name' =>
                    'Structured Error Table',

                'status' =>
                    $tableReady,

                'message' =>
                    $tableReady
                        ? 'system_error_incidents table is available.'
                        : 'Structured error table is missing.',
            ],
            [
                'name' =>
                    'Unresolved Error Incidents',

                'status' =>
                    $unresolved === 0,

                'message' =>
                    $unresolved
                    .' unresolved incident(s).',
            ],
            [
                'name' =>
                    'Unresolved Critical Errors',

                'status' =>
                    $critical === 0,

                'message' =>
                    $critical
                    .' unresolved critical incident(s).',
            ],
            [
                'name' =>
                    'Daily Log Rotation',

                'status' =>
                    $dailyLogging,

                'message' =>
                    $dailyLogging
                        ? 'Daily rotating Laravel log channel is active.'
                        : 'Laravel logs are not using daily rotation.',
            ],
            [
                'name' =>
                    'Production Debug Mode',

                'status' =>
                    $debugSafe,

                'message' =>
                    $debugSafe
                        ? 'Debug configuration is safe for the current environment.'
                        : 'APP_DEBUG must be false in production.',
            ],
        ];
    }
    private function databaseChecks(): array
    {
        try {
            DB::connection()
                ->getPdo();

            return [
                [
                    'name' =>
                        'Database Connection',

                    'status' =>
                        true,

                    'message' =>
                        'Database connection working.',
                ],
                [
                    'name' =>
                        'Database Name',

                    'status' =>
                        true,

                    'message' =>
                        config(
                            'database.connections.mysql.database',
                        )
                        ?: '-',
                ],
            ];
        } catch (Throwable $exception) {
            return [
                [
                    'name' =>
                        'Database Connection',

                    'status' =>
                        false,

                    'message' =>
                        $exception->getMessage(),
                ],
            ];
        }
    }

    private function storageChecks(): array
    {
        $publicStoragePath =
            public_path('storage');

        $backupDirectoryExists =
            Storage::disk('local')
                ->exists('backups');

        $customerDocumentsDirectoryExists =
            Storage::disk('public')
                ->exists(
                    'customer-documents',
                );

        return [
            [
                'name' =>
                    'Public Storage Link',

                'status' =>
                    file_exists(
                        $publicStoragePath,
                    ),

                'message' =>
                    file_exists(
                        $publicStoragePath,
                    )
                        ? 'public/storage link exists.'
                        : 'public/storage link missing. Run: php artisan storage:link',
            ],
            [
                'name' =>
                    'Private Backup Directory',

                'status' =>
                    $backupDirectoryExists,

                'message' =>
                    $backupDirectoryExists
                        ? 'storage/app/private/backups is available.'
                        : 'Private backup directory missing. Create one backup to generate it.',
            ],
            [
                'name' =>
                    'Customer Documents Directory',

                'status' =>
                    $customerDocumentsDirectoryExists,

                'message' =>
                    $customerDocumentsDirectoryExists
                        ? 'customer-documents directory available.'
                        : 'Directory will be created after first document upload.',
            ],
        ];
    }

    private function googleDriveChecks(): array
    {
        $enabled =
            filter_var(
                env(
                    'GOOGLE_DRIVE_BACKUP_ENABLED',
                    false,
                ),
                FILTER_VALIDATE_BOOLEAN,
            );

        $clientJson =
            base_path(
                env(
                    'GOOGLE_DRIVE_OAUTH_CLIENT_JSON',
                    'storage/app/google-drive/oauth-client.json',
                ),
            );

        $tokenJson =
            base_path(
                env(
                    'GOOGLE_DRIVE_OAUTH_TOKEN_JSON',
                    'storage/app/google-drive/oauth-token.json',
                ),
            );

        $folderId =
            env(
                'GOOGLE_DRIVE_FOLDER_ID',
            );

        return [
            [
                'name' =>
                    'Google Drive Backup Enabled',

                'status' =>
                    $enabled,

                'message' =>
                    $enabled
                        ? 'Enabled'
                        : 'Disabled',
            ],
            [
                'name' =>
                    'OAuth Client JSON',

                'status' =>
                    file_exists(
                        $clientJson,
                    ),

                'message' =>
                    file_exists(
                        $clientJson,
                    )
                        ? 'OAuth client file found.'
                        : 'OAuth client file missing.',
            ],
            [
                'name' =>
                    'OAuth Token JSON',

                'status' =>
                    file_exists(
                        $tokenJson,
                    ),

                'message' =>
                    file_exists(
                        $tokenJson,
                    )
                        ? 'Google Drive connected.'
                        : 'Google Drive not connected. Open /google-drive/oauth/redirect',
            ],
            [
                'name' =>
                    'Google Drive Folder ID',

                'status' =>
                    filled(
                        $folderId,
                    ),

                'message' =>
                    filled(
                        $folderId,
                    )
                        ? $folderId
                        : 'Folder ID blank. Backup will upload to My Drive root if allowed.',
            ],
        ];
    }

    private function routeChecks(): array
    {
        $routes = [
            'invoices.print' =>
                'Invoice Print',

            'payment-receipts.print' =>
                'Payment Receipt Print',

            'refunds.print' =>
                'Refund Print',

            'reports.export' =>
                'Reports Export',

            'database-backups.download' =>
                'Database Backup Download',

            'customer-documents.download' =>
                'Customer Documents Download',

            'google-drive.oauth.redirect' =>
                'Google Drive OAuth Redirect',

            'google-drive.oauth.callback' =>
                'Google Drive OAuth Callback',
        ];

        $checks = [];

        foreach (
            $routes
            as $routeName => $label
        ) {
            $exists =
                Route::has(
                    $routeName,
                );

            $checks[] = [
                'name' =>
                    $label,

                'status' =>
                    $exists,

                'message' =>
                    $exists
                        ? $routeName
                            .' registered.'
                        : $routeName
                            .' missing.',
            ];
        }

        return $checks;
    }

    private function tableChecks(): array
    {
        $tables = [
            'users' =>
                'Users',

            'countries' =>
                'Countries',

            'airports' =>
                'Airports',

            'airlines' =>
                'Airlines',

            'visa_types' =>
                'Visa Types',

            'suppliers' =>
                'Suppliers',

            'customers' =>
                'Customers',

            'customer_documents' =>
                'Customer Documents',

            'visa_applications' =>
                'Visa Applications',

            'flight_bookings' =>
                'Flight Bookings',

            'umrah_bookings' =>
                'Umrah Bookings',

            'hotel_bookings' =>
                'Hotel Bookings',

            'payments' =>
                'Visa Payments',

            'flight_payments' =>
                'Flight Payments',

            'umrah_payments' =>
                'Umrah Payments',

            'hotel_payments' =>
                'Hotel Payments',

            'invoices' =>
                'Invoices',

            'expenses' =>
                'Expenses',

            'expense_categories' =>
                'Expense Categories',

            'refunds' =>
                'Refunds',

            'activity_logs' =>
                'Activity Logs',

            'database_backups' =>
                'Database Backups',

            'scheduler_heartbeats' =>
                'Scheduler Heartbeats',

            'jobs' =>
                'Queue Jobs',

            'failed_jobs' =>
                'Failed Queue Jobs',

            'company_settings' =>
                'Company Settings',
        ];

        $checks = [];

        foreach (
            $tables
            as $table => $label
        ) {
            $exists =
                Schema::hasTable(
                    $table,
                );

            $checks[] = [
                'name' =>
                    $label,

                'status' =>
                    $exists,

                'message' =>
                    $exists
                        ? $table
                            .' table exists.'
                        : $table
                            .' table missing.',
            ];
        }

        return $checks;
    }
}