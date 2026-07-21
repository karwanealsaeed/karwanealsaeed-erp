<?php

namespace Tests\Feature;

use App\Filament\Resources\TravelInsurancePayments\TravelInsurancePaymentResource;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Models\Customer;
use App\Models\InsuranceProvider;
use App\Models\TravelInsurancePayment;
use App\Models\TravelInsurancePolicy;
use App\Models\User;
use App\Services\TravelInsurancePaymentApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TravelInsurancePaymentResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_resource_routes_are_registered(): void
    {
        $payment =
            $this->createPayment();

        $this->assertSame(
            '/admin/travel-insurance-payments',
            route(
                'filament.admin.resources.travel-insurance-payments.index',
                absolute: false,
            ),
        );

        $this->assertSame(
            '/admin/travel-insurance-payments/create',
            route(
                'filament.admin.resources.travel-insurance-payments.create',
                absolute: false,
            ),
        );

        $this->assertSame(
            "/admin/travel-insurance-payments/{$payment->id}",
            route(
                'filament.admin.resources.travel-insurance-payments.view',
                [
                    'record' =>
                        $payment,
                ],
                absolute: false,
            ),
        );

        $this->assertSame(
            "/admin/travel-insurance-payments/{$payment->id}/edit",
            route(
                'filament.admin.resources.travel-insurance-payments.edit',
                [
                    'record' =>
                        $payment,
                ],
                absolute: false,
            ),
        );
    }

    public function test_payment_permissions_are_registered(): void
    {
        $options =
            UserForm::getPermissionOptions();

        foreach (
            [
                'travel_insurance_payments_view',
                'travel_insurance_payments_create',
                'travel_insurance_payments_edit',
                'travel_insurance_payments_delete',
                'travel_insurance_payments_approve',
            ]
            as $permission
        ) {
            $this->assertArrayHasKey(
                $permission,
                $options,
            );
        }
    }

    public function test_admin_receives_all_payment_permissions(): void
    {
        $permissions =
            UserForm::getDefaultPermissions(
                'Admin',
            );

        foreach (
            [
                'travel_insurance_payments_view',
                'travel_insurance_payments_create',
                'travel_insurance_payments_edit',
                'travel_insurance_payments_delete',
                'travel_insurance_payments_approve',
            ]
            as $permission
        ) {
            $this->assertContains(
                $permission,
                $permissions,
            );
        }
    }

    public function test_payment_resource_requires_view_permission(): void
    {
        $user =
            $this->createUser(
                [],
            );

        $this->actingAs(
            $user,
        );

        $this->assertFalse(
            TravelInsurancePaymentResource::canAccess(),
        );

        $user->update(
            [
                'permissions' =>
                    [
                        'travel_insurance_payments_view',
                    ],
            ],
        );

        auth()->setUser(
            $user->fresh(),
        );

        $this->assertTrue(
            TravelInsurancePaymentResource::canAccess(),
        );
    }

    public function test_authorized_admin_can_render_payment_pages(): void
    {
        $admin =
            $this->createUser(
                [],
                'Admin',
            );

        $payment =
            $this->createPayment();

        $this->actingAs(
            $admin,
        );

        $this->get(
            route(
                'filament.admin.resources.travel-insurance-payments.index',
            ),
        )->assertOk();

        $this->get(
            route(
                'filament.admin.resources.travel-insurance-payments.create',
            ),
        )->assertOk();

        $this->get(
            route(
                'filament.admin.resources.travel-insurance-payments.view',
                [
                    'record' =>
                        $payment,
                ],
            ),
        )->assertOk();

        $this->get(
            route(
                'filament.admin.resources.travel-insurance-payments.edit',
                [
                    'record' =>
                        $payment,
                ],
            ),
        )->assertOk();
    }

    public function test_approval_updates_policy_paid_balance(): void
    {
        $reviewer =
            $this->createUser(
                [
                    'travel_insurance_payments_approve',
                ],
            );

        $payment =
            $this->createPayment(
                2000,
            );

        $policy =
            $payment->policy;

        $this->assertSame(
            '0.00',
            $policy->paid_amount,
        );

        app(
            TravelInsurancePaymentApprovalService::class,
        )->approve(
            $payment,
            $reviewer,
        );

        $payment->refresh();
        $policy->refresh();

        $this->assertSame(
            'Approved',
            $payment->status,
        );

        $this->assertSame(
            $reviewer->id,
            $payment->approved_by,
        );

        $this->assertNotNull(
            $payment->approved_at,
        );

        $this->assertSame(
            '2000.00',
            $policy->paid_amount,
        );

        $this->assertSame(
            '7750.00',
            $policy->balance_amount,
        );

        $this->assertSame(
            'Partial',
            $policy->payment_status,
        );
    }

    public function test_rejection_removes_payment_from_policy_balance(): void
    {
        $reviewer =
            $this->createUser(
                [
                    'travel_insurance_payments_approve',
                ],
            );

        $payment =
            $this->createPayment(
                2000,
            );

        $service =
            app(
                TravelInsurancePaymentApprovalService::class,
            );

        $service->approve(
            $payment,
            $reviewer,
        );

        $service->reject(
            $payment,
            $reviewer,
        );

        $payment->refresh();
        $payment->policy->refresh();

        $this->assertSame(
            'Rejected',
            $payment->status,
        );

        $this->assertSame(
            '0.00',
            $payment->policy->paid_amount,
        );

        $this->assertSame(
            '9750.00',
            $payment->policy->balance_amount,
        );

        $this->assertSame(
            'Unpaid',
            $payment->policy->payment_status,
        );
    }

    public function test_posted_payment_cannot_be_reviewed_or_manually_changed(): void
    {
        $admin =
            $this->createUser(
                [],
                'Admin',
            );

        $payment =
            $this->createPayment();

        $payment->update(
            [
                'posting_status' =>
                    'Posted',
            ],
        );

        $this->actingAs(
            $admin,
        );

        $this->assertFalse(
            TravelInsurancePaymentResource::canEdit(
                $payment,
            ),
        );

        $this->assertFalse(
            TravelInsurancePaymentResource::canDelete(
                $payment,
            ),
        );

        $this->assertFalse(
            TravelInsurancePaymentResource::canApprove(
                $payment,
            ),
        );

        $this->expectException(
            ValidationException::class,
        );

        app(
            TravelInsurancePaymentApprovalService::class,
        )->approve(
            $payment,
            $admin,
        );
    }

    public function test_moving_approved_payment_refreshes_old_and_new_policies(): void
    {
        $reviewer =
            $this->createUser(
                [
                    'travel_insurance_payments_approve',
                ],
            );

        $firstPolicy =
            $this->createPolicy(
                'First',
            );

        $secondPolicy =
            $this->createPolicy(
                'Second',
            );

        $payment =
            TravelInsurancePayment::query()->create(
                [
                    'travel_insurance_policy_id' =>
                        $firstPolicy->id,

                    'payment_date' =>
                        today(),

                    'amount' =>
                        3000,

                    'payment_method' =>
                        'Cash',

                    'status' =>
                        'Pending',

                    'posting_status' =>
                        'Pending',
                ],
            );

        app(
            TravelInsurancePaymentApprovalService::class,
        )->approve(
            $payment,
            $reviewer,
        );

        $firstPolicy->refresh();

        $this->assertSame(
            '3000.00',
            $firstPolicy->paid_amount,
        );

        $payment->update(
            [
                'travel_insurance_policy_id' =>
                    $secondPolicy->id,
            ],
        );

        $firstPolicy->refresh();
        $secondPolicy->refresh();

        $this->assertSame(
            '0.00',
            $firstPolicy->paid_amount,
        );

        $this->assertSame(
            '3000.00',
            $secondPolicy->paid_amount,
        );
    }

    private function createPayment(
        float $amount = 1500,
    ): TravelInsurancePayment {
        $policy =
            $this->createPolicy(
                fake()->unique()->word(),
            );

        return TravelInsurancePayment::query()->create(
            [
                'travel_insurance_policy_id' =>
                    $policy->id,

                'payment_date' =>
                    today(),

                'amount' =>
                    $amount,

                'payment_method' =>
                    'Cash',

                'status' =>
                    'Pending',

                'posting_status' =>
                    'Pending',
            ],
        );
    }

    private function createPolicy(
        string $suffix,
    ): TravelInsurancePolicy {
        $customer =
            Customer::query()->create(
                [
                    'name' =>
                        "Insurance Customer {$suffix}",

                    'mobile' =>
                        fake()->numerify(
                            '03#########',
                        ),

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
                        "Insurance Provider {$suffix}",

                    'country' =>
                        'Pakistan',

                    'status' =>
                        'Active',
                ],
            );

        return TravelInsurancePolicy::query()->create(
            [
                'customer_id' =>
                    $customer->id,

                'insurance_provider_id' =>
                    $provider->id,

                'traveller_name' =>
                    $customer->name,

                'passport_no' =>
                    fake()->unique()->bothify(
                        '??#######',
                    ),

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
                    'Draft',

                'approval_status' =>
                    'Pending',
            ],
        );
    }

    private function createUser(
        array $permissions,
        string $role = 'Sales Staff',
    ): User {
        return User::query()->create(
            [
                'name' =>
                    'Insurance Payment Test User',

                'email' =>
                    fake()->unique()->safeEmail(),

                'password' =>
                    Hash::make(
                        'password',
                    ),

                'role' =>
                    $role,

                'is_active' =>
                    true,

                'permissions' =>
                    $permissions,
            ],
        );
    }
}