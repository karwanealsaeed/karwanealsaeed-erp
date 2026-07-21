<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\InsuranceProvider;
use App\Models\TravelInsurancePolicy;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TravelInsuranceExpiryAlertCommandTest extends TestCase
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

    public function test_alert_tracking_columns_and_cast_exist(): void
    {
        $this->assertTrue(
            Schema::hasColumns(
                'travel_insurance_policies',
                [
                    'expiry_alert_fingerprint',
                    'expiry_alert_sent_at',
                ],
            ),
        );

        $casts =
            (new TravelInsurancePolicy())
                ->getCasts();

        $this->assertSame(
            'datetime',
            $casts['expiry_alert_sent_at']
            ?? null,
        );
    }

    public function test_actionable_alert_is_sent_only_to_authorized_active_staff(): void
    {
        $authorized =
            $this->user(
                true,
                [
                    'travel_insurance_policies_view',
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
                    'travel_insurance_policies_view',
                ],
            );

        $policy =
            $this->policy(
                [
                    'policy_end_date' =>
                        today()->addDays(
                            20,
                        ),
                ],
            );

        $this->artisan(
            'travel-insurance:send-expiry-alerts',
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
            'Policy Expiring Within 30 Days',
            json_encode(
                $notification->data,
                JSON_THROW_ON_ERROR,
            ),
        );

        $policy =
            $policy->fresh();

        $this->assertNotNull(
            $policy->expiry_alert_fingerprint,
        );

        $this->assertNotNull(
            $policy->expiry_alert_sent_at,
        );
    }

    public function test_same_alert_is_not_sent_twice(): void
    {
        $user =
            $this->user(
                true,
                [
                    'travel_insurance_policies_view',
                ],
            );

        $this->policy(
            [
                'policy_end_date' =>
                    today()->addDays(
                        20,
                    ),
            ],
        );

        $this->artisan(
            'travel-insurance:send-expiry-alerts',
        )->assertSuccessful();

        $this->artisan(
            'travel-insurance:send-expiry-alerts',
        )->assertSuccessful();

        $this->assertSame(
            1,
            $user
                ->notifications()
                ->count(),
        );
    }

    public function test_dry_run_does_not_send_or_mark_alert(): void
    {
        $user =
            $this->user(
                true,
                [
                    'travel_insurance_policies_view',
                ],
            );

        $policy =
            $this->policy(
                [
                    'policy_end_date' =>
                        today()->addDays(
                            5,
                        ),
                ],
            );

        $this->artisan(
            'travel-insurance:send-expiry-alerts',
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

        $policy =
            $policy->fresh();

        $this->assertNull(
            $policy->expiry_alert_fingerprint,
        );

        $this->assertNull(
            $policy->expiry_alert_sent_at,
        );
    }

    public function test_resolved_alert_tracking_is_reset(): void
    {
        $this->user(
            true,
            [
                'travel_insurance_policies_view',
            ],
        );

        $policy =
            $this->policy(
                [
                    'policy_end_date' =>
                        today()->addDays(
                            60,
                        ),

                    'expiry_alert_fingerprint' =>
                        str_repeat(
                            'a',
                            64,
                        ),

                    'expiry_alert_sent_at' =>
                        now(),
                ],
            );

        $this->artisan(
            'travel-insurance:send-expiry-alerts',
        )->assertSuccessful();

        $policy =
            $policy->fresh();

        $this->assertNull(
            $policy->expiry_alert_fingerprint,
        );

        $this->assertNull(
            $policy->expiry_alert_sent_at,
        );
    }

    public function test_changed_expiry_bucket_sends_new_alert(): void
    {
        $user =
            $this->user(
                true,
                [
                    'travel_insurance_policies_view',
                ],
            );

        $policy =
            $this->policy(
                [
                    'policy_end_date' =>
                        today()->addDays(
                            20,
                        ),
                ],
            );

        $this->artisan(
            'travel-insurance:send-expiry-alerts',
        )->assertSuccessful();

        $firstFingerprint =
            $policy
                ->fresh()
                ->expiry_alert_fingerprint;

        $policy->forceFill(
            [
                'policy_end_date' =>
                    today()->addDays(
                        5,
                    ),
            ],
        )->saveQuietly();

        $this->artisan(
            'travel-insurance:send-expiry-alerts',
        )->assertSuccessful();

        $this->assertSame(
            2,
            $user
                ->notifications()
                ->count(),
        );

        $this->assertNotSame(
            $firstFingerprint,
            $policy
                ->fresh()
                ->expiry_alert_fingerprint,
        );
    }

    public function test_expired_policy_sends_danger_alert(): void
    {
        $user =
            $this->user(
                true,
                [
                    'travel_insurance_policies_view',
                ],
            );

        $policy =
            $this->policy(
                [
                    'policy_end_date' =>
                        today()->subDay(),

                    'status' =>
                        'Expired',
                ],
            );

        $this->artisan(
            'travel-insurance:send-expiry-alerts',
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
            'Policy Expired',
            json_encode(
                $notification->data,
                JSON_THROW_ON_ERROR,
            ),
        );

        $this->assertNotNull(
            $policy
                ->fresh()
                ->expiry_alert_sent_at,
        );
    }

    public function test_unapproved_and_cancelled_policies_are_skipped(): void
    {
        $user =
            $this->user(
                true,
                [
                    'travel_insurance_policies_view',
                ],
            );

        $pending =
            $this->policy(
                [
                    'approval_status' =>
                        'Pending',

                    'policy_end_date' =>
                        today()->addDays(
                            5,
                        ),
                ],
            );

        $cancelled =
            $this->policy(
                [
                    'status' =>
                        'Cancelled',

                    'policy_end_date' =>
                        today()->addDays(
                            5,
                        ),
                ],
            );

        $this->artisan(
            'travel-insurance:send-expiry-alerts',
        )->assertSuccessful();

        $this->assertSame(
            0,
            $user
                ->notifications()
                ->count(),
        );

        $this->assertNull(
            $pending
                ->fresh()
                ->expiry_alert_fingerprint,
        );

        $this->assertNull(
            $cancelled
                ->fresh()
                ->expiry_alert_fingerprint,
        );
    }

    public function test_scheduler_contains_daily_insurance_expiry_command(): void
    {
        $schedule =
            app(
                Schedule::class,
            );

        $event =
            collect(
                $schedule->events(),
            )->first(
                function (
                    $event,
                ): bool {
                    return str_contains(
                        (string) $event->command,
                        'travel-insurance:send-expiry-alerts',
                    );
                },
            );

        $this->assertNotNull(
            $event,
        );

        $this->assertSame(
            '15 8 * * *',
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

    private function policy(
        array $attributes = [],
    ): TravelInsurancePolicy {
        self::$sequence++;

        $sequence =
            self::$sequence;

        $customer =
            Customer::query()->create(
                [
                    'name' =>
                        "Insurance Expiry Customer {$sequence}",

                    'mobile' =>
                        '0305'
                        .str_pad(
                            (string) $sequence,
                            7,
                            '0',
                            STR_PAD_LEFT,
                        ),

                    'email' =>
                        "insurance-expiry-{$sequence}@example.test",

                    'customer_type' =>
                        'Agent',

                    'status' =>
                        'Active',
                ],
            );

        $provider =
            InsuranceProvider::query()->create(
                [
                    'name' =>
                        "Insurance Expiry Provider {$sequence}",

                    'country' =>
                        'Pakistan',

                    'status' =>
                        'Active',
                ],
            );

        return TravelInsurancePolicy::query()->create(
            array_merge(
                [
                    'policy_no' =>
                        "TIP-EXPIRY-{$sequence}",

                    'customer_id' =>
                        $customer->id,

                    'insurance_provider_id' =>
                        $provider->id,

                    'traveller_name' =>
                        "Expiry Traveller {$sequence}",

                    'passport_no' =>
                        "EXPIRY{$sequence}",

                    'destination_country' =>
                        'Saudi Arabia',

                    'trip_type' =>
                        'Single Trip',

                    'coverage_type' =>
                        'Comprehensive',

                    'coverage_amount' =>
                        50000,

                    'coverage_currency' =>
                        'USD',

                    'travel_start_date' =>
                        today()->subDays(
                            10,
                        ),

                    'travel_end_date' =>
                        today()->addDays(
                            10,
                        ),

                    'policy_start_date' =>
                        today()->subDays(
                            10,
                        ),

                    'policy_end_date' =>
                        today()->addDays(
                            20,
                        ),

                    'premium_amount' =>
                        10000,

                    'supplier_cost' =>
                        7000,

                    'discount' =>
                        500,

                    'tax' =>
                        250,

                    'currency' =>
                        'PKR',

                    'status' =>
                        'Active',

                    'approval_status' =>
                        'Approved',
                ],
                $attributes,
            ),
        );
    }
}