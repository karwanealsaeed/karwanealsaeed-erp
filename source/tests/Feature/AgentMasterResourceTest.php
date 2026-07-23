<?php

namespace Tests\Feature;

use App\Filament\Resources\Agents\AgentResource;
use App\Filament\Resources\Agents\Pages\CreateAgent;
use App\Filament\Resources\Agents\Pages\EditAgent;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Models\Customer;
use App\Models\User;
use App\Support\RbacPermissionRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;
use ReflectionMethod;
use Tests\TestCase;

class AgentMasterResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_business_fields_and_model_contract_exist(): void
    {
        foreach (
            [
                'agency_name',
                'commission_rate',
                'credit_limit',
            ]
            as $column
        ) {
            $this->assertTrue(
                Schema::hasColumn(
                    'customers',
                    $column,
                ),
            );
        }

        $agent = new Customer();

        $this->assertContains(
            'agency_name',
            $agent->getFillable(),
        );

        $this->assertContains(
            'commission_rate',
            $agent->getFillable(),
        );

        $this->assertContains(
            'credit_limit',
            $agent->getFillable(),
        );

        $this->assertSame(
            'decimal:2',
            $agent->getCasts()['commission_rate'],
        );

        $this->assertSame(
            'decimal:2',
            $agent->getCasts()['credit_limit'],
        );
    }

    public function test_agent_resource_query_never_returns_non_agents(): void
    {
        $agent =
            Customer::query()
                ->create(
                    $this->customerData(
                        'Agent',
                        '03000000001',
                    ),
                );

        $customer =
            Customer::query()
                ->create(
                    $this->customerData(
                        'Customer',
                        '03000000002',
                    ),
                );

        $this->assertTrue(
            AgentResource::getEloquentQuery()
                ->whereKey($agent)
                ->exists(),
        );

        $this->assertFalse(
            AgentResource::getEloquentQuery()
                ->whereKey($customer)
                ->exists(),
        );

        $this->assertSame(
            [$agent->id],
            AgentResource::getEloquentQuery()
                ->pluck('id')
                ->all(),
        );
    }

    public function test_create_and_edit_pages_force_agent_discriminator(): void
    {
        $create =
            (new ReflectionClass(
                CreateAgent::class,
            ))->newInstanceWithoutConstructor();

        $createMethod =
            new ReflectionMethod(
                CreateAgent::class,
                'mutateFormDataBeforeCreate',
            );

        $createdData =
            $createMethod->invoke(
                $create,
                [
                    'name' => 'Test Contact',
                    'customer_type' => 'Customer',
                ],
            );

        $this->assertSame(
            'Agent',
            $createdData['customer_type'],
        );

        $edit =
            (new ReflectionClass(
                EditAgent::class,
            ))->newInstanceWithoutConstructor();

        $editMethod =
            new ReflectionMethod(
                EditAgent::class,
                'mutateFormDataBeforeSave',
            );

        $savedData =
            $editMethod->invoke(
                $edit,
                [
                    'name' => 'Test Contact',
                    'customer_type' => 'Customer',
                ],
            );

        $this->assertSame(
            'Agent',
            $savedData['customer_type'],
        );
    }

    public function test_agent_permissions_follow_customer_role_parity(): void
    {
        $options =
            UserForm::getPermissionOptions();

        foreach (
            [
                'agents_view',
                'agents_create',
                'agents_edit',
                'agents_delete',
            ]
            as $permission
        ) {
            $this->assertArrayHasKey(
                $permission,
                $options,
            );
        }

        foreach (
            [
                'Manager',
                'Accountant',
                'Sales Staff',
            ]
            as $role
        ) {
            $defaults =
                UserForm::getDefaultPermissions(
                    $role,
                );

            foreach (
                [
                    'view',
                    'create',
                    'edit',
                    'delete',
                ]
                as $action
            ) {
                $customerPermission =
                    "customers_{$action}";

                $agentPermission =
                    "agents_{$action}";

                $this->assertSame(
                    in_array(
                        $customerPermission,
                        $defaults,
                        true,
                    ),
                    in_array(
                        $agentPermission,
                        $defaults,
                        true,
                    ),
                    "{$role} permission parity failed for {$action}.",
                );
            }
        }

        $adminDefaults =
            UserForm::getDefaultPermissions(
                'Admin',
            );

        $this->assertContains(
            'agents_delete',
            $adminDefaults,
        );
    }

    public function test_record_permissions_are_agent_and_permission_aware(): void
    {
        $agent =
            Customer::query()
                ->create(
                    $this->customerData(
                        'Agent',
                        '03000000003',
                    ),
                );

        $customer =
            Customer::query()
                ->create(
                    $this->customerData(
                        'Customer',
                        '03000000004',
                    ),
                );

        $user =
            User::factory()
                ->create([
                    'role' =>
                        RbacPermissionRegistry::ROLE_SALES_STAFF,
                    'is_active' =>
                        true,
                    'permissions' => [
                        'agents_view',
                        'agents_edit',
                    ],
                ]);

        $this->actingAs($user);

        $this->assertTrue(
            AgentResource::canAccess(),
        );

        $this->assertTrue(
            AgentResource::canView($agent),
        );

        $this->assertTrue(
            AgentResource::canEdit($agent),
        );

        $this->assertFalse(
            AgentResource::canDelete($agent),
        );

        $this->assertFalse(
            AgentResource::canView($customer),
        );

        $this->assertFalse(
            AgentResource::canEdit($customer),
        );

        $this->assertFalse(
            AgentResource::canDelete($customer),
        );
    }

    public function test_agent_resource_routes_are_registered(): void
    {
        foreach (
            [
                'filament.admin.resources.agents.index',
                'filament.admin.resources.agents.create',
                'filament.admin.resources.agents.view',
                'filament.admin.resources.agents.edit',
            ]
            as $routeName
        ) {
            $this->assertTrue(
                Route::has($routeName),
                "Missing route: {$routeName}",
            );
        }
    }

    private function customerData(
        string $type,
        string $mobile,
    ): array {
        return [
            'name' =>
                $type.' Contact',
            'agency_name' =>
                $type === 'Agent'
                    ? 'Test Travel Agency'
                    : null,
            'customer_type' =>
                $type,
            'mobile' =>
                $mobile,
            'country' =>
                'Pakistan',
            'status' =>
                'Active',
        ];
    }
}