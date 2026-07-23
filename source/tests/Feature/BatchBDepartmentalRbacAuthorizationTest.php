<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Concerns\HasRbacResourceAuthorization;
use App\Filament\Resources\SupportInquiries\SupportInquiryResource;
use App\Filament\Resources\SupportTickets\SupportTicketResource;
use App\Filament\Resources\UmrahGroupFlights\UmrahGroupFlightResource;
use App\Filament\Resources\UmrahHotels\UmrahHotelResource;
use App\Filament\Resources\UmrahPackages\UmrahPackageResource;
use App\Filament\Resources\UmrahTransportRoutes\UmrahTransportRouteResource;
use App\Filament\Resources\UmrahVouchers\UmrahVoucherResource;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Models\User;
use App\Support\RbacPermissionRegistry;
use ReflectionClass;
use Tests\TestCase;

final class BatchBDepartmentalRbacAuthorizationTest extends TestCase
{
    public function test_dedicated_permission_catalogue_and_groups_are_complete(): void
    {
        $options =
            UserForm::getPermissionOptions();

        $groups =
            UserForm::getPermissionGroups();

        $this->assertCount(
            235,
            $options,
        );

        $this->assertCount(
            10,
            $groups,
        );

        foreach ($this->resources() as $contract) {
            foreach (
                [
                    'view',
                    'create',
                    'edit',
                    'delete',
                ]
                as $action
            ) {
                $permission =
                    $contract['prefix']
                    .'_'
                    .$action;

                $this->assertArrayHasKey(
                    $permission,
                    $options,
                );

                $this->assertArrayHasKey(
                    $permission,
                    $groups[
                        $contract['group']
                    ]['options'],
                );
            }
        }

        $this->assertArrayHasKey(
            'umrah_vouchers_print',
            $options,
        );

        $this->assertArrayHasKey(
            'umrah_vouchers_print',
            $groups[
                'permissions_print'
            ]['options'],
        );
    }

    public function test_base_role_defaults_preserve_legacy_action_compatibility(): void
    {
        $defaults = [
            RbacPermissionRegistry::ROLE_MANAGER =>
                UserForm::getDefaultPermissions(
                    RbacPermissionRegistry::ROLE_MANAGER,
                ),
            RbacPermissionRegistry::ROLE_ACCOUNTANT =>
                UserForm::getDefaultPermissions(
                    RbacPermissionRegistry::ROLE_ACCOUNTANT,
                ),
            RbacPermissionRegistry::ROLE_SALES_STAFF =>
                UserForm::getDefaultPermissions(
                    RbacPermissionRegistry::ROLE_SALES_STAFF,
                ),
        ];

        foreach ($this->resources() as $contract) {
            foreach (
                [
                    'view',
                    'create',
                    'edit',
                    'delete',
                ]
                as $action
            ) {
                $permission =
                    $contract['prefix']
                    .'_'
                    .$action;

                $this->assertSame(
                    $action !== 'delete',
                    in_array(
                        $permission,
                        $defaults[
                            RbacPermissionRegistry::ROLE_MANAGER
                        ],
                        true,
                    ),
                );

                $this->assertSame(
                    $action === 'view',
                    in_array(
                        $permission,
                        $defaults[
                            RbacPermissionRegistry::ROLE_ACCOUNTANT
                        ],
                        true,
                    ),
                );

                $this->assertSame(
                    $action !== 'delete',
                    in_array(
                        $permission,
                        $defaults[
                            RbacPermissionRegistry::ROLE_SALES_STAFF
                        ],
                        true,
                    ),
                );
            }
        }

        foreach ($defaults as $permissions) {
            $this->assertContains(
                'umrah_vouchers_print',
                $permissions,
            );
        }
    }

    public function test_all_batch_b_resources_use_central_trait_and_registry(): void
    {
        $registered =
            RbacPermissionRegistry::resourceClasses();

        foreach ($this->resources() as $resourceClass => $contract) {
            $this->assertContains(
                HasRbacResourceAuthorization::class,
                class_uses_recursive(
                    $resourceClass,
                ),
            );

            $this->assertContains(
                $resourceClass,
                $registered,
            );
        }

        $this->assertCount(
            39,
            $registered,
        );
    }

    public function test_registry_rules_use_exact_dedicated_actions_and_role_matrix(): void
    {
        $reflection =
            new ReflectionClass(
                RbacPermissionRegistry::class,
            );

        $rules =
            $reflection
                ->getReflectionConstant(
                    'RESOURCE_RULES',
                )
                ?->getValue();

        $this->assertIsArray($rules);

        foreach ($this->resources() as $resourceClass => $contract) {
            $this->assertArrayHasKey(
                $resourceClass,
                $rules,
            );

            $this->assertSame(
                [
                    RbacPermissionRegistry::ROLE_MANAGER,
                    RbacPermissionRegistry::ROLE_ACCOUNTANT,
                    RbacPermissionRegistry::ROLE_SALES_STAFF,
                ],
                $rules[$resourceClass]['roles'],
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
                $this->assertSame(
                    [
                        $contract['prefix']
                        .'_'
                        .$action,
                    ],
                    $rules[
                        $resourceClass
                    ]['actions'][$action],
                );
            }
        }
    }

    public function test_runtime_access_requires_exact_permission_and_allowed_role(): void
    {
        foreach ($this->resources() as $resourceClass => $contract) {
            foreach (
                [
                    'view',
                    'create',
                    'edit',
                    'delete',
                ]
                as $action
            ) {
                $permission =
                    $contract['prefix']
                    .'_'
                    .$action;

                $this->assertTrue(
                    RbacPermissionRegistry::allowsResourceAction(
                        $this->user(
                            RbacPermissionRegistry::ROLE_MANAGER,
                            [$permission],
                        ),
                        $resourceClass,
                        $action,
                    ),
                );

                $this->assertFalse(
                    RbacPermissionRegistry::allowsResourceAction(
                        $this->user(
                            RbacPermissionRegistry::ROLE_MANAGER,
                            [],
                        ),
                        $resourceClass,
                        $action,
                    ),
                );

                $this->assertTrue(
                    RbacPermissionRegistry::allowsResourceAction(
                        $this->user(
                            RbacPermissionRegistry::ROLE_SALES_STAFF,
                            [$permission],
                        ),
                        $resourceClass,
                        $action,
                    ),
                );
            }
        }
    }

    public function test_legacy_borrowed_permissions_and_methods_are_removed(): void
    {
        foreach ($this->resources() as $resourceClass => $contract) {
            $reflection =
                new ReflectionClass(
                    $resourceClass,
                );

            $path =
                $reflection->getFileName();

            $this->assertIsString($path);

            $source =
                file_get_contents($path);

            $this->assertIsString($source);

            $this->assertStringContainsString(
                'HasRbacResourceAuthorization',
                $source,
            );

            $this->assertStringNotContainsString(
                'umrah_bookings.',
                $source,
            );

            $this->assertStringNotContainsString(
                'customers.',
                $source,
            );

            $this->assertStringNotContainsString(
                'customers_',
                $source,
            );

            foreach (
                [
                    'canAccess',
                    'canViewAny',
                    'canCreate',
                    'canEdit',
                    'canDelete',
                ]
                as $method
            ) {
                $this->assertDoesNotMatchRegularExpression(
                    '/public\s+static\s+function\s+'
                    .preg_quote($method, '/')
                    .'\s*\(/',
                    $source,
                );
            }
        }
    }

    public function test_support_mutations_and_voucher_print_are_explicitly_guarded(): void
    {
        $inquiryTable =
            file_get_contents(
                app_path(
                    'Filament/Resources/SupportInquiries/Tables/SupportInquiriesTable.php',
                ),
            );

        $ticketTable =
            file_get_contents(
                app_path(
                    'Filament/Resources/SupportTickets/Tables/SupportTicketsTable.php',
                ),
            );

        $voucherTable =
            file_get_contents(
                app_path(
                    'Filament/Resources/UmrahVouchers/Tables/UmrahVouchersTable.php',
                ),
            );

        $controller =
            file_get_contents(
                app_path(
                    'Http/Controllers/UmrahVoucherPrintController.php',
                ),
            );

        $this->assertIsString($inquiryTable);
        $this->assertIsString($ticketTable);
        $this->assertIsString($voucherTable);
        $this->assertIsString($controller);

        $this->assertStringContainsString(
            'SupportInquiryResource::canEdit($record)',
            $inquiryTable,
        );

        $this->assertSame(
            2,
            substr_count(
                $ticketTable,
                'SupportTicketResource::canEdit($record)',
            ),
        );

        $this->assertStringContainsString(
            'UmrahVoucherResource::canEdit($record)',
            $voucherTable,
        );

        $this->assertStringContainsString(
            'UmrahVoucherResource::canDelete($record)',
            $voucherTable,
        );

        $this->assertStringContainsString(
            "'umrah_vouchers.print'",
            $voucherTable,
        );

        $this->assertStringContainsString(
            "'umrah_vouchers.print'",
            $controller,
        );

        $this->assertStringNotContainsString(
            "'umrah_bookings.view'",
            $controller,
        );
    }

    public function test_admin_bypass_inactive_rejection_and_no_default_delete_are_enforced(): void
    {
        foreach ($this->resources() as $resourceClass => $contract) {
            $deletePermission =
                $contract['prefix']
                .'_delete';

            $this->assertTrue(
                RbacPermissionRegistry::allowsResourceAction(
                    $this->user(
                        RbacPermissionRegistry::ROLE_ADMIN,
                        [],
                    ),
                    $resourceClass,
                    'delete',
                ),
            );

            $inactive =
                $this->user(
                    RbacPermissionRegistry::ROLE_MANAGER,
                    [$deletePermission],
                );

            $inactive->is_active =
                false;

            $this->assertFalse(
                RbacPermissionRegistry::allowsResourceAction(
                    $inactive,
                    $resourceClass,
                    'delete',
                ),
            );

            foreach (
                [
                    RbacPermissionRegistry::ROLE_MANAGER,
                    RbacPermissionRegistry::ROLE_ACCOUNTANT,
                    RbacPermissionRegistry::ROLE_SALES_STAFF,
                ]
                as $role
            ) {
                $this->assertNotContains(
                    $deletePermission,
                    UserForm::getDefaultPermissions(
                        $role,
                    ),
                );
            }
        }
    }

    /**
     * @return array<class-string, array{
     *     prefix: string,
     *     group: string
     * }>
     */
    private function resources(): array
    {
        return [
            UmrahGroupFlightResource::class => [
                'prefix' =>
                    'umrah_group_flights',
                'group' =>
                    'permissions_bookings',
            ],
            UmrahHotelResource::class => [
                'prefix' =>
                    'umrah_hotels',
                'group' =>
                    'permissions_bookings',
            ],
            UmrahPackageResource::class => [
                'prefix' =>
                    'umrah_packages',
                'group' =>
                    'permissions_bookings',
            ],
            UmrahTransportRouteResource::class => [
                'prefix' =>
                    'umrah_transport_routes',
                'group' =>
                    'permissions_bookings',
            ],
            UmrahVoucherResource::class => [
                'prefix' =>
                    'umrah_vouchers',
                'group' =>
                    'permissions_bookings',
            ],
            SupportInquiryResource::class => [
                'prefix' =>
                    'support_inquiries',
                'group' =>
                    'permissions_customers',
            ],
            SupportTicketResource::class => [
                'prefix' =>
                    'support_tickets',
                'group' =>
                    'permissions_customers',
            ],
        ];
    }

    private function user(
        string $role,
        array $permissions,
    ): User {
        $user =
            new User();

        $user->forceFill([
            'role' =>
                $role,
            'permissions' =>
                $permissions,
            'is_active' =>
                true,
        ]);

        return $user;
    }
}