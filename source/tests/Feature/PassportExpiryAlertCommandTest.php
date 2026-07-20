<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VisaApplication;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PassportExpiryAlertCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(
            '2026-07-20 08:00:00',
        );
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_alert_tracking_columns_exist(): void
    {
        $this->assertTrue(
            Schema::hasColumns(
                'visa_applications',
                [
                    'passport_alert_fingerprint',
                    'passport_alert_sent_at',
                ],
            ),
        );
    }

    public function test_actionable_alert_is_sent_to_authorized_active_staff(): void
    {
        $admin = $this->user(
            role: 'Admin',
        );

        $manager = $this->user(
            role: 'Manager',
        );

        $sales = $this->user(
            role: 'Sales Staff',
            permissions: [
                'visa_applications_view',
            ],
        );

        $unauthorized = $this->user(
            role: 'Accountant',
            permissions: [],
        );

        $inactive = $this->user(
            role: 'Sales Staff',
            permissions: [
                'visa_applications_view',
            ],
            active: false,
        );

        $application =
            $this->application();

        Artisan::call(
            'passport:send-expiry-alerts',
        );

        $this->assertDatabaseCount(
            'notifications',
            3,
        );

        foreach (
            [
                $admin,
                $manager,
                $sales,
            ] as $recipient
        ) {
            $this->assertDatabaseHas(
                'notifications',
                [
                    'notifiable_type' =>
                        User::class,

                    'notifiable_id' =>
                        $recipient->id,
                ],
            );
        }

        foreach (
            [
                $unauthorized,
                $inactive,
            ] as $nonRecipient
        ) {
            $this->assertDatabaseMissing(
                'notifications',
                [
                    'notifiable_type' =>
                        User::class,

                    'notifiable_id' =>
                        $nonRecipient->id,
                ],
            );
        }

        $application->refresh();

        $this->assertSame(
            'Renewal Required',
            $application
                ->passport_validity_status,
        );

        $this->assertNotNull(
            $application
                ->passport_alert_fingerprint,
        );

        $this->assertNotNull(
            $application
                ->passport_alert_sent_at,
        );

        $notificationData =
            json_decode(
                DB::table('notifications')
                    ->where(
                        'notifiable_id',
                        $admin->id,
                    )
                    ->value('data'),
                true,
            );

        $this->assertStringContainsString(
            'Passport Alert',
            $notificationData['title'],
        );

        $this->assertStringContainsString(
            $application->application_no,
            $notificationData['body'],
        );
    }

    public function test_same_alert_is_not_sent_twice(): void
    {
        $this->user(
            role: 'Admin',
        );

        $application =
            $this->application();

        Artisan::call(
            'passport:send-expiry-alerts',
        );

        $firstFingerprint =
            $application
                ->fresh()
                ->passport_alert_fingerprint;

        Artisan::call(
            'passport:send-expiry-alerts',
        );

        $this->assertDatabaseCount(
            'notifications',
            1,
        );

        $this->assertSame(
            $firstFingerprint,
            $application
                ->fresh()
                ->passport_alert_fingerprint,
        );
    }

    public function test_dry_run_does_not_send_or_mark_alert(): void
    {
        $this->user(
            role: 'Admin',
        );

        $application =
            $this->application();

        Artisan::call(
            'passport:send-expiry-alerts',
            [
                '--dry-run' => true,
            ],
        );

        $this->assertDatabaseCount(
            'notifications',
            0,
        );

        $application->refresh();

        $this->assertNull(
            $application
                ->passport_alert_fingerprint,
        );

        $this->assertNull(
            $application
                ->passport_alert_sent_at,
        );
    }

    public function test_resolved_alert_is_reset(): void
    {
        $this->user(
            role: 'Admin',
        );

        $application =
            $this->application([
                'passport_expiry_date' =>
                    '2028-12-31',

                'passport_alert_fingerprint' =>
                    str_repeat('a', 64),

                'passport_alert_sent_at' =>
                    now(),
            ]);

        Artisan::call(
            'passport:send-expiry-alerts',
        );

        $application->refresh();

        $this->assertSame(
            'Valid',
            $application
                ->passport_validity_status,
        );

        $this->assertNull(
            $application
                ->passport_alert_fingerprint,
        );

        $this->assertNull(
            $application
                ->passport_alert_sent_at,
        );

        $this->assertDatabaseCount(
            'notifications',
            0,
        );
    }

    public function test_changed_alert_fingerprint_sends_new_alert(): void
    {
        $this->user(
            role: 'Admin',
        );

        $application =
            $this->application();

        Artisan::call(
            'passport:send-expiry-alerts',
        );

        $firstFingerprint =
            $application
                ->fresh()
                ->passport_alert_fingerprint;

        $application->forceFill([
            'passport_expiry_date' =>
                '2026-08-05',
        ])->saveQuietly();

        Artisan::call(
            'passport:send-expiry-alerts',
        );

        $application->refresh();

        $this->assertDatabaseCount(
            'notifications',
            2,
        );

        $this->assertNotSame(
            $firstFingerprint,
            $application
                ->passport_alert_fingerprint,
        );

        $this->assertSame(
            'Not Valid for Journey',
            $application
                ->passport_validity_status,
        );
    }

    public function test_missing_passport_expiry_sends_alert(): void
    {
        $this->user(
            role: 'Admin',
        );

        $application =
            $this->application([
                'passport_expiry_date' =>
                    null,
            ]);

        Artisan::call(
            'passport:send-expiry-alerts',
        );

        $application->refresh();

        $this->assertDatabaseCount(
            'notifications',
            1,
        );

        $this->assertSame(
            'Missing Passport Expiry',
            $application
                ->passport_validity_status,
        );
    }

    public function test_valid_passport_expiring_within_180_days_sends_warning(): void
    {
        $this->user(
            role: 'Admin',
        );

        $application =
            $this->application([
                'passport_expiry_date' =>
                    '2026-11-01',

                'required_passport_validity_months' =>
                    0,

                'travel_date' =>
                    '2026-07-25',

                'return_date' =>
                    '2026-07-26',
            ]);

        Artisan::call(
            'passport:send-expiry-alerts',
        );

        $application->refresh();

        $this->assertSame(
            'Valid',
            $application
                ->passport_validity_status,
        );

        $this->assertNotNull(
            $application
                ->passport_alert_fingerprint,
        );

        $this->assertDatabaseCount(
            'notifications',
            1,
        );

        $data = json_decode(
            DB::table('notifications')
                ->value('data'),
            true,
        );

        $this->assertStringContainsString(
            'Passport Expiring Soon',
            $data['title'],
        );
    }

    public function test_scheduler_contains_daily_passport_alert_command(): void
    {
        $routes = file_get_contents(
            base_path(
                'routes/console.php',
            ),
        );

        $this->assertStringContainsString(
            'passport:send-expiry-alerts',
            $routes,
        );

        $this->assertStringContainsString(
            "->dailyAt('08:00')",
            $routes,
        );

        $this->assertStringContainsString(
            "->timezone('Asia/Karachi')",
            $routes,
        );
    }

    private function user(
        string $role,
        array $permissions = [],
        bool $active = true,
    ): User {
        return User::factory()->create([
            'role' =>
                $role,

            'is_active' =>
                $active,

            'permissions' =>
                $permissions,
        ]);
    }

    private function application(
        array $overrides = [],
    ): VisaApplication {
        $token = strtoupper(
            substr(
                hash(
                    'sha256',
                    uniqid('', true),
                ),
                0,
                10,
            ),
        );

        $countryId =
            $this->insertRequiredRow(
                'countries',
                [
                    'name' =>
                        'Test Country '.$token,

                    'country_name' =>
                        'Test Country '.$token,

                    'code' =>
                        substr($token, 0, 3),

                    'status' =>
                        'Active',
                ],
            );

        $visaTypeId =
            $this->insertRequiredRow(
                'visa_types',
                [
                    'name' =>
                        'Test Visa '.$token,

                    'title' =>
                        'Test Visa '.$token,

                    'country_id' =>
                        $countryId,

                    'status' =>
                        'Active',
                ],
            );

        $customerId =
            $this->insertRequiredRow(
                'customers',
                [
                    'name' =>
                        'Passport Customer '
                        .$token,

                    'mobile' =>
                        '03'
                        .substr(
                            preg_replace(
                                '/\D/',
                                '',
                                hexdec(
                                    substr(
                                        $token,
                                        0,
                                        6,
                                    ),
                                ),
                            )
                            .'000000000',
                            0,
                            9,
                        ),

                    'passport_no' =>
                        'P-'.$token,

                    'status' =>
                        'Active',
                ],
            );

        $attributes = array_merge(
            [
                'application_no' =>
                    'VA-'.$token,

                'customer_id' =>
                    $customerId,

                'country_id' =>
                    $countryId,

                'visa_type_id' =>
                    $visaTypeId,

                'passport_no' =>
                    'P-'.$token,

                'passport_expiry_date' =>
                    '2026-12-01',

                'required_passport_validity_months' =>
                    6,

                'travel_date' =>
                    '2026-08-01',

                'return_date' =>
                    '2026-08-10',

                'status' =>
                    'Applied',
            ],
            $overrides,
        );

        $id = $this->insertRequiredRow(
            'visa_applications',
            $attributes,
        );

        return VisaApplication::query()
            ->findOrFail($id);
    }

    private function insertRequiredRow(
        string $table,
        array $overrides,
    ): int {
        $columns =
            Schema::getColumns($table);

        $columnNames = collect($columns)
            ->pluck('name')
            ->all();

        $data = [];

        foreach ($overrides as $name => $value) {
            if (
                in_array(
                    $name,
                    $columnNames,
                    true,
                )
            ) {
                $data[$name] = $value;
            }
        }

        $token = hash(
            'sha256',
            $table
            .uniqid('', true),
        );

        foreach ($columns as $column) {
            $name =
                $column['name'];

            if (
                array_key_exists(
                    $name,
                    $data,
                )
            ) {
                continue;
            }

            if (
                in_array(
                    $name,
                    [
                        'id',
                        'created_at',
                        'updated_at',
                        'deleted_at',
                    ],
                    true,
                )
            ) {
                continue;
            }

            if (
                ($column[
                    'auto_increment'
                ] ?? false) === true
            ) {
                continue;
            }

            if (
                ($column['nullable'] ?? false)
                === true
            ) {
                continue;
            }

            if (
                array_key_exists(
                    'default',
                    $column,
                )
                && $column['default'] !== null
            ) {
                continue;
            }

            $type = strtolower(
                (string) (
                    $column['type_name']
                    ?? $column['type']
                    ?? 'string'
                ),
            );

            $data[$name] =
                $this->defaultColumnValue(
                    $name,
                    $type,
                    $token,
                );
        }

        if (
            in_array(
                'created_at',
                $columnNames,
                true,
            )
        ) {
            $data['created_at'] =
                now();
        }

        if (
            in_array(
                'updated_at',
                $columnNames,
                true,
            )
        ) {
            $data['updated_at'] =
                now();
        }

        return (int) DB::table($table)
            ->insertGetId($data);
    }

    private function defaultColumnValue(
        string $name,
        string $type,
        string $token,
    ): mixed {
        if (
            str_contains($type, 'int')
            || str_contains($type, 'decimal')
            || str_contains($type, 'numeric')
            || str_contains($type, 'float')
            || str_contains($type, 'double')
            || str_contains($type, 'real')
        ) {
            return 0;
        }

        if (
            str_contains($type, 'bool')
        ) {
            return true;
        }

        if (
            str_contains($type, 'date')
            || str_contains($type, 'time')
        ) {
            return now();
        }

        if (
            str_contains($type, 'json')
        ) {
            return json_encode(
                [],
                JSON_THROW_ON_ERROR,
            );
        }

        if (
            str_contains($name, 'email')
        ) {
            return substr(
                $token,
                0,
                12,
            )
            .'@example.test';
        }

        if (
            str_contains($name, 'mobile')
            || str_contains($name, 'phone')
        ) {
            return '0300'
                .substr(
                    preg_replace(
                        '/\D/',
                        '',
                        hexdec(
                            substr(
                                $token,
                                0,
                                7,
                            ),
                        ),
                    )
                    .'0000000',
                    0,
                    7,
                );
        }

        return strtoupper(
            substr(
                $token,
                0,
                12,
            ),
        );
    }
}
