<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Customer;
use App\Models\User;
use App\Models\VisaApplication;
use App\Models\VisaType;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class VisaExpiryAlertTest extends TestCase
{
    use RefreshDatabase;

    private static int $sequence =
        0;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(
            Carbon::parse(
                '2026-07-21 09:00:00',
                'Asia/Karachi',
            ),
        );
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_visa_expiry_columns_model_casts_and_ui_are_available(): void
    {
        $this->assertTrue(
            Schema::hasColumns(
                'visa_applications',
                [
                    'visa_issue_date',
                    'visa_expiry_date',
                    'visa_alert_fingerprint',
                    'visa_alert_sent_at',
                ],
            ),
        );

        $model =
            new VisaApplication();

        $fillable =
            $model->getFillable();

        $casts =
            $model->getCasts();

        foreach (
            [
                'visa_issue_date',
                'visa_expiry_date',
                'visa_alert_fingerprint',
                'visa_alert_sent_at',
            ]
            as $field
        ) {
            $this->assertContains(
                $field,
                $fillable,
            );
        }

        $this->assertSame(
            'date',
            $casts['visa_issue_date']
            ?? null,
        );

        $this->assertSame(
            'date',
            $casts['visa_expiry_date']
            ?? null,
        );

        $this->assertSame(
            'datetime',
            $casts['visa_alert_sent_at']
            ?? null,
        );

        foreach (
            [
                app_path(
                    'Filament/Resources/VisaApplications/Schemas/VisaApplicationForm.php',
                ),
                app_path(
                    'Filament/Resources/VisaApplications/Schemas/VisaApplicationInfolist.php',
                ),
                app_path(
                    'Filament/Resources/VisaApplications/Tables/VisaApplicationsTable.php',
                ),
            ]
            as $sourcePath
        ) {
            $source =
                file_get_contents(
                    $sourcePath,
                );

            $this->assertStringContainsString(
                'visa_issue_date',
                $source,
            );

            $this->assertStringContainsString(
                'visa_expiry_date',
                $source,
            );
        }
    }

    public function test_authorized_staff_can_render_visa_resource_pages(): void
    {
        $user =
            $this->user(
                true,
                [
                    'visa_applications_view',
                ],
            );

        $application =
            $this->application();

        $this->actingAs(
            $user,
        )
            ->get(
                route(
                    'filament.admin.resources.visa-applications.index',
                ),
            )
            ->assertOk();

        $this->actingAs(
            $user,
        )
            ->get(
                route(
                    'filament.admin.resources.visa-applications.view',
                    [
                        'record' =>
                            $application,
                    ],
                ),
            )
            ->assertOk();
    }

    public function test_customer_portal_shows_only_own_visa_expiry_details(): void
    {
        $first =
            $this->customer();

        $second =
            $this->customer();

        $firstApplication =
            $this->application(
                [
                    'customer_id' =>
                        $first->id,

                    'application_no' =>
                        'VISA-PORTAL-OWN',

                    'visa_issue_date' =>
                        now(
                            'Asia/Karachi',
                        )->subMonth(),

                    'visa_expiry_date' =>
                        now(
                            'Asia/Karachi',
                        )->addDays(
                            20,
                        ),

                    'visa_alert_fingerprint' =>
                        str_repeat(
                            'a',
                            64,
                        ),
                ],
            );

        $secondApplication =
            $this->application(
                [
                    'customer_id' =>
                        $second->id,

                    'application_no' =>
                        'VISA-PORTAL-OTHER',
                ],
            );

        $this->withSession(
            $this->portalSession(
                $first,
            ),
        )
            ->get(
                route(
                    'customer.portal.visa',
                ),
            )
            ->assertOk()
            ->assertSee(
                $firstApplication->application_no,
            )
            ->assertSee(
                $firstApplication
                    ->visa_expiry_date
                    ->format(
                        'd M Y',
                    ),
            )
            ->assertDontSee(
                $secondApplication->application_no,
            )
            ->assertDontSee(
                'visa_alert_fingerprint',
            )
            ->assertDontSee(
                str_repeat(
                    'a',
                    64,
                ),
            );
    }

    public function test_actionable_alert_is_sent_only_to_authorized_active_staff(): void
    {
        $authorized =
            $this->user(
                true,
                [
                    'visa_applications_view',
                ],
            );

        $unauthorized =
            $this->user(
                true,
                [],
            );

        $inactive =
            $this->user(
                false,
                [
                    'visa_applications_view',
                ],
            );

        $application =
            $this->application(
                [
                    'visa_expiry_date' =>
                        now(
                            'Asia/Karachi',
                        )->addDays(
                            20,
                        ),
                ],
            );

        $this->artisan(
            'visa:send-expiry-alerts',
        )->assertSuccessful();

        $this->assertSame(
            1,
            $authorized
                ->notifications()
                ->count(),
        );

        $this->assertSame(
            0,
            $unauthorized
                ->notifications()
                ->count(),
        );

        $this->assertSame(
            0,
            $inactive
                ->notifications()
                ->count(),
        );

        $notification =
            $authorized
                ->notifications()
                ->first();

        $this->assertNotNull(
            $notification,
        );

        $this->assertStringContainsString(
            'Visa Expiring Within 30 Days',
            json_encode(
                $notification->data,
                JSON_THROW_ON_ERROR,
            ),
        );

        $application =
            $application->fresh();

        $this->assertNotNull(
            $application->visa_alert_fingerprint,
        );

        $this->assertNotNull(
            $application->visa_alert_sent_at,
        );
    }

    public function test_same_visa_alert_is_not_sent_twice(): void
    {
        $user =
            $this->user(
                true,
                [
                    'visa_applications_view',
                ],
            );

        $this->application(
            [
                'visa_expiry_date' =>
                    now(
                        'Asia/Karachi',
                    )->addDays(
                        20,
                    ),
            ],
        );

        $this->artisan(
            'visa:send-expiry-alerts',
        )->assertSuccessful();

        $this->artisan(
            'visa:send-expiry-alerts',
        )->assertSuccessful();

        $this->assertSame(
            1,
            $user
                ->notifications()
                ->count(),
        );
    }

    public function test_visa_alert_dry_run_does_not_send_or_mark(): void
    {
        $user =
            $this->user(
                true,
                [
                    'visa_applications_view',
                ],
            );

        $application =
            $this->application(
                [
                    'visa_expiry_date' =>
                        now(
                            'Asia/Karachi',
                        )->addDays(
                            5,
                        ),
                ],
            );

        $this->artisan(
            'visa:send-expiry-alerts',
            [
                '--dry-run' =>
                    true,
            ],
        )->assertSuccessful();

        $this->assertSame(
            0,
            $user
                ->notifications()
                ->count(),
        );

        $application =
            $application->fresh();

        $this->assertNull(
            $application->visa_alert_fingerprint,
        );

        $this->assertNull(
            $application->visa_alert_sent_at,
        );
    }

    public function test_changed_visa_expiry_bucket_sends_new_alert(): void
    {
        $user =
            $this->user(
                true,
                [
                    'visa_applications_view',
                ],
            );

        $application =
            $this->application(
                [
                    'visa_expiry_date' =>
                        now(
                            'Asia/Karachi',
                        )->addDays(
                            20,
                        ),
                ],
            );

        $this->artisan(
            'visa:send-expiry-alerts',
        )->assertSuccessful();

        $firstFingerprint =
            $application
                ->fresh()
                ->visa_alert_fingerprint;

        $application->forceFill(
            [
                'visa_expiry_date' =>
                    now(
                        'Asia/Karachi',
                    )->addDays(
                        5,
                    ),
            ],
        )->saveQuietly();

        $this->artisan(
            'visa:send-expiry-alerts',
        )->assertSuccessful();

        $this->assertSame(
            2,
            $user
                ->notifications()
                ->count(),
        );

        $this->assertNotSame(
            $firstFingerprint,
            $application
                ->fresh()
                ->visa_alert_fingerprint,
        );
    }

    public function test_expired_visa_sends_danger_alert(): void
    {
        $user =
            $this->user(
                true,
                [
                    'visa_applications_view',
                ],
            );

        $application =
            $this->application(
                [
                    'status' =>
                        'Delivered',

                    'visa_expiry_date' =>
                        now(
                            'Asia/Karachi',
                        )->subDay(),
                ],
            );

        $this->artisan(
            'visa:send-expiry-alerts',
        )->assertSuccessful();

        $this->assertSame(
            1,
            $user
                ->notifications()
                ->count(),
        );

        $notification =
            $user
                ->notifications()
                ->first();

        $this->assertStringContainsString(
            'Visa Expired',
            json_encode(
                $notification->data,
                JSON_THROW_ON_ERROR,
            ),
        );

        $this->assertNotNull(
            $application
                ->fresh()
                ->visa_alert_sent_at,
        );
    }

    public function test_missing_visa_expiry_sends_alert(): void
    {
        $user =
            $this->user(
                true,
                [
                    'visa_applications_view',
                ],
            );

        $application =
            $this->application(
                [
                    'visa_expiry_date' =>
                        null,
                ],
            );

        $this->artisan(
            'visa:send-expiry-alerts',
        )->assertSuccessful();

        $this->assertSame(
            1,
            $user
                ->notifications()
                ->count(),
        );

        $notification =
            $user
                ->notifications()
                ->first();

        $this->assertStringContainsString(
            'Visa Expiry Missing',
            json_encode(
                $notification->data,
                JSON_THROW_ON_ERROR,
            ),
        );

        $this->assertNotNull(
            $application
                ->fresh()
                ->visa_alert_fingerprint,
        );
    }

    public function test_resolved_alert_is_reset_and_inactive_status_is_skipped(): void
    {
        $user =
            $this->user(
                true,
                [
                    'visa_applications_view',
                ],
            );

        $resolved =
            $this->application(
                [
                    'visa_expiry_date' =>
                        now(
                            'Asia/Karachi',
                        )->addDays(
                            90,
                        ),

                    'visa_alert_fingerprint' =>
                        str_repeat(
                            'b',
                            64,
                        ),

                    'visa_alert_sent_at' =>
                        now(
                            'Asia/Karachi',
                        ),
                ],
            );

        $draft =
            $this->application(
                [
                    'status' =>
                        'Draft',

                    'visa_expiry_date' =>
                        now(
                            'Asia/Karachi',
                        )->addDays(
                            5,
                        ),
                ],
            );

        $this->artisan(
            'visa:send-expiry-alerts',
        )->assertSuccessful();

        $resolved =
            $resolved->fresh();

        $this->assertNull(
            $resolved->visa_alert_fingerprint,
        );

        $this->assertNull(
            $resolved->visa_alert_sent_at,
        );

        $this->assertNull(
            $draft
                ->fresh()
                ->visa_alert_fingerprint,
        );

        $this->assertSame(
            0,
            $user
                ->notifications()
                ->count(),
        );
    }

    public function test_scheduler_contains_daily_visa_expiry_command(): void
    {
        $schedule =
            app(
                Schedule::class,
            );

        $events =
            collect(
                $schedule->events(),
            )
                ->filter(
                    function (
                        $event,
                    ): bool {
                        return str_contains(
                            (string) $event->command,
                            'visa:send-expiry-alerts',
                        );
                    },
                )
                ->values();

        $this->assertCount(
            1,
            $events,
        );

        $event =
            $events->first();

        $this->assertSame(
            '30 8 * * *',
            $event->expression,
        );

        $this->assertTrue(
            (bool) $event->withoutOverlapping,
        );
    }

    private function user(
        bool $active,
        array $permissions,
    ): User {
        return User::factory()->create(
            [
                'role' =>
                    'Accountant',

                'is_active' =>
                    $active,

                'permissions' =>
                    $permissions,
            ],
        );
    }

    private function customer(): Customer
    {
        self::$sequence++;

        $sequence =
            self::$sequence;

        return Customer::query()->create(
            [
                'name' =>
                    "Visa Expiry Customer {$sequence}",

                'mobile' =>
                    '0306'
                    .str_pad(
                        (string) $sequence,
                        7,
                        '0',
                        STR_PAD_LEFT,
                    ),

                'email' =>
                    "visa-expiry-{$sequence}@example.test",

                'customer_type' =>
                    'Agent',

                'status' =>
                    'Active',

                'portal_password' =>
                    Hash::make(
                        'PortalPassword123!',
                    ),

                'portal_registered_at' =>
                    now(
                        'Asia/Karachi',
                    ),
            ],
        );
    }

    private function country(): Country
    {
        self::$sequence++;

        $sequence =
            self::$sequence;

        $countryId =
            DB::table(
                'countries',
            )->insertGetId(
                [
                    'name' =>
                        "Visa Country {$sequence}",

                    'code' =>
                        'VC'
                        .$sequence,

                    'iso3' =>
                        'VS'
                        .($sequence % 10),

                    'currency' =>
                        'SAR',

                    'phone_code' =>
                        '+966',

                    'status' =>
                        true,

                    'created_at' =>
                        now(
                            'Asia/Karachi',
                        ),

                    'updated_at' =>
                        now(
                            'Asia/Karachi',
                        ),
                ],
            );

        return Country::query()
            ->findOrFail(
                $countryId,
            );
    }
    private function visaType(
        Country $country,
    ): VisaType {
        self::$sequence++;

        $sequence =
            self::$sequence;

        $visaTypeId =
            DB::table(
                'visa_types',
            )->insertGetId(
                [
                    'name' =>
                        "Visit Visa {$sequence}",

                    'description' =>
                        'Visa expiry alert feature test fixture.',

                    'status' =>
                        true,

                    'created_at' =>
                        now(
                            'Asia/Karachi',
                        ),

                    'updated_at' =>
                        now(
                            'Asia/Karachi',
                        ),
                ],
            );

        return VisaType::query()
            ->findOrFail(
                $visaTypeId,
            );
    }
    private function application(
        array $attributes = [],
    ): VisaApplication {
        self::$sequence++;

        $sequence =
            self::$sequence;

        $customer =
            isset(
                $attributes['customer_id'],
            )
                ? Customer::query()
                    ->findOrFail(
                        $attributes['customer_id'],
                    )
                : $this->customer();

        $country =
            $this->country();

        $visaType =
            $this->visaType(
                $country,
            );

        return VisaApplication::query()->create(
            array_merge(
                [
                    'application_no' =>
                        "VISA-EXPIRY-{$sequence}",

                    'customer_id' =>
                        $customer->id,

                    'country_id' =>
                        $country->id,

                    'visa_type_id' =>
                        $visaType->id,

                    'passport_no' =>
                        "VISA{$sequence}",

                    'passport_expiry_date' =>
                        now(
                            'Asia/Karachi',
                        )->addYear(),

                    'required_passport_validity_months' =>
                        6,

                    'travel_date' =>
                        now(
                            'Asia/Karachi',
                        )->addDays(
                            10,
                        ),

                    'return_date' =>
                        now(
                            'Asia/Karachi',
                        )->addDays(
                            20,
                        ),

                    'visa_issue_date' =>
                        now(
                            'Asia/Karachi',
                        )->subMonth(),

                    'visa_expiry_date' =>
                        now(
                            'Asia/Karachi',
                        )->addDays(
                            20,
                        ),

                    'visa_fee' =>
                        10000,

                    'service_fee' =>
                        2000,

                    'total_amount' =>
                        12000,

                    'paid_amount' =>
                        0,

                    'balance_amount' =>
                        12000,

                    'status' =>
                        'Approved',

                    'passport_status' =>
                        'Received',
                ],
                $attributes,
            ),
        );
    }

    private function portalSession(
        Customer $customer,
    ): array {
        return [
            'customer_portal_id' =>
                $customer->id,

            'customer_portal_name' =>
                $customer->name,

            'customer_portal_last_activity' =>
                time(),

            'customer_portal_password_fingerprint' =>
                hash(
                    'sha256',
                    $customer->portal_password,
                ),
        ];
    }
}
