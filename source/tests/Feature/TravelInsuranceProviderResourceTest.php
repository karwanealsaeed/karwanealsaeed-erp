<?php

namespace Tests\Feature;

use App\Filament\Resources\InsuranceProviders\InsuranceProviderResource;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Models\InsuranceProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TravelInsuranceProviderResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_insurance_provider_resource_routes_are_registered(): void
    {
        $provider =
            InsuranceProvider::query()->create(
                [
                    'name' =>
                        'Route Test Insurance Provider',

                    'status' =>
                        'Active',

                    'country' =>
                        'Pakistan',
                ],
            );

        $this->assertSame(
            '/admin/insurance-providers',
            route(
                'filament.admin.resources.insurance-providers.index',
                absolute: false,
            ),
        );

        $this->assertSame(
            '/admin/insurance-providers/create',
            route(
                'filament.admin.resources.insurance-providers.create',
                absolute: false,
            ),
        );

        $this->assertSame(
            "/admin/insurance-providers/{$provider->id}",
            route(
                'filament.admin.resources.insurance-providers.view',
                [
                    'record' =>
                        $provider,
                ],
                absolute: false,
            ),
        );

        $this->assertSame(
            "/admin/insurance-providers/{$provider->id}/edit",
            route(
                'filament.admin.resources.insurance-providers.edit',
                [
                    'record' =>
                        $provider,
                ],
                absolute: false,
            ),
        );
    }

    public function test_insurance_provider_permissions_are_registered(): void
    {
        $options =
            UserForm::getPermissionOptions();

        $this->assertArrayHasKey(
            'insurance_providers_view',
            $options,
        );

        $this->assertArrayHasKey(
            'insurance_providers_create',
            $options,
        );

        $this->assertArrayHasKey(
            'insurance_providers_edit',
            $options,
        );

        $this->assertArrayHasKey(
            'insurance_providers_delete',
            $options,
        );
    }

    public function test_admin_receives_all_insurance_provider_permissions(): void
    {
        $permissions =
            UserForm::getDefaultPermissions(
                'Admin',
            );

        $this->assertContains(
            'insurance_providers_view',
            $permissions,
        );

        $this->assertContains(
            'insurance_providers_create',
            $permissions,
        );

        $this->assertContains(
            'insurance_providers_edit',
            $permissions,
        );

        $this->assertContains(
            'insurance_providers_delete',
            $permissions,
        );
    }

    public function test_resource_access_requires_view_permission(): void
    {
        $user =
            $this->createUser(
                [],
            );

        $this->actingAs(
            $user,
        );

        $this->assertFalse(
            InsuranceProviderResource::canAccess(),
        );

        $user->update(
            [
                'permissions' =>
                    [
                        'insurance_providers_view',
                    ],
            ],
        );

        auth()->setUser(
            $user->fresh(),
        );

        $this->assertTrue(
            InsuranceProviderResource::canAccess(),
        );
    }

    public function test_authorized_admin_can_render_provider_pages(): void
    {
        $admin =
            $this->createUser(
                [],
                'Admin',
            );

        $provider =
            InsuranceProvider::query()->create(
                [
                    'name' =>
                        'Render Test Provider',

                    'company_name' =>
                        'Render Test Insurance Company',

                    'phone' =>
                        '03000000000',

                    'email' =>
                        'provider@example.test',

                    'country' =>
                        'Pakistan',

                    'status' =>
                        'Active',
                ],
            );

        $this->actingAs(
            $admin,
        );

        $this->get(
            route(
                'filament.admin.resources.insurance-providers.index',
            ),
        )->assertOk();

        $this->get(
            route(
                'filament.admin.resources.insurance-providers.create',
            ),
        )->assertOk();

        $this->get(
            route(
                'filament.admin.resources.insurance-providers.view',
                [
                    'record' =>
                        $provider,
                ],
            ),
        )->assertOk();

        $this->get(
            route(
                'filament.admin.resources.insurance-providers.edit',
                [
                    'record' =>
                        $provider,
                ],
            ),
        )->assertOk();
    }

    private function createUser(
        array $permissions,
        string $role = 'Sales Staff',
    ): User {
        return User::query()->create(
            [
                'name' =>
                    'Insurance Provider Test User',

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