<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\InsuranceProvider;
use App\Models\Invoice;
use App\Models\TravelInsurancePayment;
use App\Models\TravelInsurancePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerPortalTravelInsuranceTest extends TestCase
{
    use RefreshDatabase;

    private static int $sequence =
        0;

    public function test_portal_insurance_routes_are_registered_and_protected(): void
    {
        $routeNames = [
            'customer.portal.travel_insurance',
            'customer.portal.travel_insurance.policy_document',
            'customer.portal.travel_insurance.payment_receipt',
        ];

        foreach ($routeNames as $routeName) {
            $this->assertTrue(
                Route::has(
                    $routeName,
                ),
            );

            $route =
                Route::getRoutes()
                    ->getByName(
                        $routeName,
                    );

            $this->assertNotNull(
                $route,
            );

            $this->assertContains(
                'customer.portal',
                $route->gatherMiddleware(),
            );
        }

        $this->assertContains(
            'throttle:30,1',
            Route::getRoutes()
                ->getByName(
                    'customer.portal.travel_insurance.policy_document',
                )
                ->gatherMiddleware(),
        );

        $this->assertContains(
            'throttle:30,1',
            Route::getRoutes()
                ->getByName(
                    'customer.portal.travel_insurance.payment_receipt',
                )
                ->gatherMiddleware(),
        );
    }

    public function test_guest_is_redirected_to_portal_login(): void
    {
        $this->get(
            route(
                'customer.portal.travel_insurance',
            ),
        )->assertRedirect(
            route(
                'customer.portal.login',
            ),
        );
    }

    public function test_customer_sees_only_own_approved_policies(): void
    {
        $customer =
            $this->agent();

        $otherCustomer =
            $this->agent();

        $visiblePolicy =
            $this->policy(
                $customer,
                [
                    'traveller_name' =>
                        'Visible Insurance Traveller',
                ],
            );

        $pendingPolicy =
            $this->policy(
                $customer,
                [
                    'traveller_name' =>
                        'Pending Internal Traveller',

                    'approval_status' =>
                        'Pending',
                ],
            );

        $otherPolicy =
            $this->policy(
                $otherCustomer,
                [
                    'traveller_name' =>
                        'Another Customer Traveller',
                ],
            );

        $this->withSession(
            $this->portalSession(
                $customer,
            ),
        )
            ->get(
                route(
                    'customer.portal.travel_insurance',
                ),
            )
            ->assertOk()
            ->assertSee(
                $visiblePolicy->policy_no,
            )
            ->assertSee(
                'Visible Insurance Traveller',
            )
            ->assertDontSee(
                $pendingPolicy->policy_no,
            )
            ->assertDontSee(
                'Pending Internal Traveller',
            )
            ->assertDontSee(
                $otherPolicy->policy_no,
            )
            ->assertDontSee(
                'Another Customer Traveller',
            );
    }

    public function test_policy_history_is_paginated_and_customer_scoped(): void
    {
        $customer =
            $this->agent();

        for ($index = 1; $index <= 13; $index++) {
            $this->policy(
                $customer,
                [
                    'traveller_name' =>
                        "Pagination Traveller {$index}",
                ],
            );
        }

        $this->withSession(
            $this->portalSession(
                $customer,
            ),
        )
            ->get(
                route(
                    'customer.portal.travel_insurance',
                ),
            )
            ->assertOk()
            ->assertViewHas(
                'policies',
                function (
                    mixed $policies,
                ): bool {
                    return $policies instanceof
                        LengthAwarePaginator
                        && $policies->perPage() === 12
                        && $policies->total() === 13;
                },
            );
    }

    public function test_customer_can_download_only_own_policy_document(): void
    {
        Storage::fake(
            'local',
        );

        $customer =
            $this->agent();

        $otherCustomer =
            $this->agent();

        $ownedPath =
            'travel-insurance/policies/owned-policy.pdf';

        $otherPath =
            'travel-insurance/policies/other-policy.pdf';

        Storage::disk(
            'local',
        )->put(
            $ownedPath,
            'owned-policy-content',
        );

        Storage::disk(
            'local',
        )->put(
            $otherPath,
            'other-policy-content',
        );

        $ownedPolicy =
            $this->policy(
                $customer,
                [
                    'policy_document_path' =>
                        $ownedPath,

                    'policy_document_name' =>
                        'owned-policy.pdf',
                ],
            );

        $otherPolicy =
            $this->policy(
                $otherCustomer,
                [
                    'policy_document_path' =>
                        $otherPath,

                    'policy_document_name' =>
                        'other-policy.pdf',
                ],
            );

        $session =
            $this->portalSession(
                $customer,
            );

        $this->withSession(
            $session,
        )
            ->get(
                route(
                    'customer.portal.travel_insurance.policy_document',
                    $ownedPolicy,
                ),
            )
            ->assertOk()
            ->assertHeader(
                'x-content-type-options',
                'nosniff',
            );

        $this->withSession(
            $session,
        )
            ->get(
                route(
                    'customer.portal.travel_insurance.policy_document',
                    $otherPolicy,
                ),
            )
            ->assertForbidden();
    }

    public function test_customer_can_download_only_own_approved_payment_receipt(): void
    {
        Storage::fake(
            'local',
        );

        $customer =
            $this->agent();

        $otherCustomer =
            $this->agent();

        $ownedPolicy =
            $this->policy(
                $customer,
            );

        $otherPolicy =
            $this->policy(
                $otherCustomer,
            );

        $ownedPath =
            'travel-insurance/payments/owned-receipt.pdf';

        $otherPath =
            'travel-insurance/payments/other-receipt.pdf';

        Storage::disk(
            'local',
        )->put(
            $ownedPath,
            'owned-receipt-content',
        );

        Storage::disk(
            'local',
        )->put(
            $otherPath,
            'other-receipt-content',
        );

        $ownedPayment =
            $this->payment(
                $ownedPolicy,
                [
                    'receipt_path' =>
                        $ownedPath,

                    'receipt_name' =>
                        'owned-receipt.pdf',
                ],
            );

        $otherPayment =
            $this->payment(
                $otherPolicy,
                [
                    'receipt_path' =>
                        $otherPath,

                    'receipt_name' =>
                        'other-receipt.pdf',
                ],
            );

        $pendingPayment =
            $this->payment(
                $ownedPolicy,
                [
                    'status' =>
                        'Pending',

                    'posting_status' =>
                        'Pending',

                    'receipt_path' =>
                        $ownedPath,

                    'receipt_name' =>
                        'pending-receipt.pdf',
                ],
            );

        $session =
            $this->portalSession(
                $customer,
            );

        $this->withSession(
            $session,
        )
            ->get(
                route(
                    'customer.portal.travel_insurance.payment_receipt',
                    $ownedPayment,
                ),
            )
            ->assertOk();

        $this->withSession(
            $session,
        )
            ->get(
                route(
                    'customer.portal.travel_insurance.payment_receipt',
                    $otherPayment,
                ),
            )
            ->assertForbidden();

        $this->withSession(
            $session,
        )
            ->get(
                route(
                    'customer.portal.travel_insurance.payment_receipt',
                    $pendingPayment,
                ),
            )
            ->assertNotFound();
    }

    public function test_portal_shows_linked_invoice_without_exposing_storage_paths(): void
    {
        $customer =
            $this->agent();

        $policy =
            $this->policy(
                $customer,
                [
                    'policy_document_path' =>
                        'travel-insurance/policies/secret-policy.pdf',

                    'policy_document_name' =>
                        'policy-document.pdf',
                ],
            );

        $invoice =
            Invoice::query()->create(
                [
                    'invoice_no' =>
                        'INV-PORTAL-001',

                    'customer_id' =>
                        $customer->id,

                    'invoice_date' =>
                        today(),

                    'due_date' =>
                        today()->addDays(
                            10,
                        ),

                    'service_type' =>
                        'Travel Insurance',

                    'service_reference' =>
                        $policy->policy_no,

                    'sub_total' =>
                        10000,

                    'discount' =>
                        500,

                    'tax' =>
                        250,

                    'total_amount' =>
                        9750,

                    'paid_amount' =>
                        2000,

                    'balance_amount' =>
                        7750,

                    'status' =>
                        'Partially Paid',
                ],
            );

        $policy->forceFill(
            [
                'invoice_id' =>
                    $invoice->id,
            ],
        )->saveQuietly();

        $this->payment(
            $policy,
            [
                'receipt_path' =>
                    'travel-insurance/payments/secret-receipt.pdf',

                'receipt_name' =>
                    'receipt-document.pdf',
            ],
        );

        $this->withSession(
            $this->portalSession(
                $customer,
            ),
        )
            ->get(
                route(
                    'customer.portal.travel_insurance',
                ),
            )
            ->assertOk()
            ->assertSee(
                'INV-PORTAL-001',
            )
            ->assertSee(
                route(
                    'customer.portal.invoices',
                ),
                false,
            )
            ->assertDontSee(
                'travel-insurance/policies/secret-policy.pdf',
            )
            ->assertDontSee(
                'travel-insurance/payments/secret-receipt.pdf',
            )
            ->assertDontSee(
                'policy_document_path',
            )
            ->assertDontSee(
                'receipt_path',
            );
    }

    public function test_unsafe_or_missing_private_path_returns_not_found(): void
    {
        Storage::fake(
            'local',
        );

        $customer =
            $this->agent();

        $unsafePolicy =
            $this->policy(
                $customer,
                [
                    'policy_document_path' =>
                        '../outside/private.pdf',

                    'policy_document_name' =>
                        'private.pdf',
                ],
            );

        $missingPolicy =
            $this->policy(
                $customer,
                [
                    'policy_document_path' =>
                        'travel-insurance/policies/missing.pdf',

                    'policy_document_name' =>
                        'missing.pdf',
                ],
            );

        $session =
            $this->portalSession(
                $customer,
            );

        $this->withSession(
            $session,
        )
            ->get(
                route(
                    'customer.portal.travel_insurance.policy_document',
                    $unsafePolicy,
                ),
            )
            ->assertNotFound();

        $this->withSession(
            $session,
        )
            ->get(
                route(
                    'customer.portal.travel_insurance.policy_document',
                    $missingPolicy,
                ),
            )
            ->assertNotFound();
    }

    private function agent(): Customer
    {
        self::$sequence++;

        $sequence =
            str_pad(
                (string) self::$sequence,
                4,
                '0',
                STR_PAD_LEFT,
            );

        return Customer::query()->create(
            [
                'name' =>
                    "Insurance Portal Agent {$sequence}",

                'mobile' =>
                    "0300000{$sequence}",

                'email' =>
                    "insurance-portal-{$sequence}@example.test",

                'customer_type' =>
                    'Agent',

                'status' =>
                    'Active',

                'portal_password' =>
                    Hash::make(
                        'PortalPassword123!',
                    ),

                'portal_registered_at' =>
                    now(),
            ],
        );
    }

    private function policy(
        Customer $customer,
        array $attributes = [],
    ): TravelInsurancePolicy {
        self::$sequence++;

        $sequence =
            self::$sequence;

        $provider =
            InsuranceProvider::query()->create(
                [
                    'name' =>
                        "Portal Insurance Provider {$sequence}",

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
                        "TIP-PORTAL-{$sequence}",

                    'customer_id' =>
                        $customer->id,

                    'insurance_provider_id' =>
                        $provider->id,

                    'traveller_name' =>
                        "Insurance Traveller {$sequence}",

                    'passport_no' =>
                        "PORTAL{$sequence}",

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
                        today()->addDays(
                            10,
                        ),

                    'travel_end_date' =>
                        today()->addDays(
                            20,
                        ),

                    'policy_start_date' =>
                        today()->addDays(
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

    private function payment(
        TravelInsurancePolicy $policy,
        array $attributes = [],
    ): TravelInsurancePayment {
        self::$sequence++;

        $sequence =
            self::$sequence;

        return TravelInsurancePayment::query()->create(
            array_merge(
                [
                    'payment_no' =>
                        "TIPAY-PORTAL-{$sequence}",

                    'travel_insurance_policy_id' =>
                        $policy->id,

                    'payment_date' =>
                        today(),

                    'amount' =>
                        2000,

                    'payment_method' =>
                        'Cash',

                    'reference_no' =>
                        "REF-{$sequence}",

                    'status' =>
                        'Approved',

                    'posting_status' =>
                        'Pending',
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