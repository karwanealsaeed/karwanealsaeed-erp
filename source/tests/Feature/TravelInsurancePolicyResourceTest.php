<?php

namespace Tests\Feature;

use App\Filament\Resources\TravelInsurancePolicies\TravelInsurancePolicyResource;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Models\Customer;
use App\Models\InsuranceProvider;
use App\Models\TravelInsurancePolicy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TravelInsurancePolicyResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_policy_resource_routes_are_registered(): void
    {
        $policy =
            $this->createPolicy();

        $this->assertSame(
            '/admin/travel-insurance-policies',
            route(
                'filament.admin.resources.travel-insurance-policies.index',
                absolute: false,
            ),
        );

        $this->assertSame(
            '/admin/travel-insurance-policies/create',
            route(
                'filament.admin.resources.travel-insurance-policies.create',
                absolute: false,
            ),
        );

        $this->assertSame(
            "/admin/travel-insurance-policies/{$policy->id}",
            route(
                'filament.admin.resources.travel-insurance-policies.view',
                [
                    'record' =>
                        $policy,
                ],
                absolute: false,
            ),
        );

        $this->assertSame(
            "/admin/travel-insurance-policies/{$policy->id}/edit",
            route(
                'filament.admin.resources.travel-insurance-policies.edit',
                [
                    'record' =>
                        $policy,
                ],
                absolute: false,
            ),
        );
    }

    public function test_policy_permissions_are_registered(): void
    {
        $options =
            UserForm::getPermissionOptions();

        foreach (
            [
                'travel_insurance_policies_view',
                'travel_insurance_policies_create',
                'travel_insurance_policies_edit',
                'travel_insurance_policies_delete',
            ]
            as $permission
        ) {
            $this->assertArrayHasKey(
                $permission,
                $options,
            );
        }
    }

    public function test_admin_receives_all_policy_permissions(): void
    {
        $permissions =
            UserForm::getDefaultPermissions(
                'Admin',
            );

        foreach (
            [
                'travel_insurance_policies_view',
                'travel_insurance_policies_create',
                'travel_insurance_policies_edit',
                'travel_insurance_policies_delete',
            ]
            as $permission
        ) {
            $this->assertContains(
                $permission,
                $permissions,
            );
        }
    }

    public function test_policy_resource_requires_view_permission(): void
    {
        $user =
            $this->createUser(
                [],
            );

        $this->actingAs(
            $user,
        );

        $this->assertFalse(
            TravelInsurancePolicyResource::canAccess(),
        );

        $user->update(
            [
                'permissions' =>
                    [
                        'travel_insurance_policies_view',
                    ],
            ],
        );

        auth()->setUser(
            $user->fresh(),
        );

        $this->assertTrue(
            TravelInsurancePolicyResource::canAccess(),
        );
    }

    public function test_authorized_admin_can_render_policy_pages(): void
    {
        $admin =
            $this->createUser(
                [],
                'Admin',
            );

        $policy =
            $this->createPolicy();

        $this->actingAs(
            $admin,
        );

        $this->get(
            route(
                'filament.admin.resources.travel-insurance-policies.index',
            ),
        )->assertOk();

        $this->get(
            route(
                'filament.admin.resources.travel-insurance-policies.create',
            ),
        )->assertOk();

        $this->get(
            route(
                'filament.admin.resources.travel-insurance-policies.view',
                [
                    'record' =>
                        $policy,
                ],
            ),
        )->assertOk();

        $this->get(
            route(
                'filament.admin.resources.travel-insurance-policies.edit',
                [
                    'record' =>
                        $policy,
                ],
            ),
        )->assertOk();
    }

    private function createPolicy(): TravelInsurancePolicy
    {
        $customer =
            Customer::query()->create(
                [
                    'name' =>
                        'Policy Resource Customer',

                    'mobile' =>
                        '03000000000',

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
                        'Policy Resource Provider',

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
                    'PA1234567',

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
                    'Policy Resource Test User',

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