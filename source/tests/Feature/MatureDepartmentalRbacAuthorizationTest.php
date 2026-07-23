<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Concerns\HasRbacResourceAuthorization;
use App\Filament\Resources\Airlines\AirlineResource;
use App\Filament\Resources\Airports\AirportResource;
use App\Filament\Resources\BlogPosts\BlogPostResource;
use App\Filament\Resources\Cities\CityResource;
use App\Filament\Resources\Countries\CountryResource;
use App\Filament\Resources\Faqs\FaqResource;
use App\Filament\Resources\FlightBookings\FlightBookingResource;
use App\Filament\Resources\FlightPayments\FlightPaymentResource;
use App\Filament\Resources\GalleryItems\GalleryItemResource;
use App\Filament\Resources\PakistanTours\PakistanTourResource;
use App\Filament\Resources\Promotions\PromotionResource;
use App\Filament\Resources\Testimonials\TestimonialResource;
use App\Filament\Resources\UmrahBookings\UmrahBookingResource;
use App\Filament\Resources\UmrahPayments\UmrahPaymentResource;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\VisaApplications\VisaApplicationResource;
use App\Filament\Resources\VisaTypes\VisaTypeResource;
use App\Models\User;
use App\Support\RbacPermissionRegistry;
use ReflectionClass;
use Tests\TestCase;

final class MatureDepartmentalRbacAuthorizationTest extends TestCase
{
    public function test_all_batch_a_resources_use_central_trait_and_registry(): void
    {
        $registered =
            RbacPermissionRegistry::resourceClasses();

        foreach ($this->resources() as $resourceClass => $contract) {
            $this->assertContains(
                HasRbacResourceAuthorization::class,
                $this->recursiveClassUses(
                    $resourceClass,
                ),
                "{$resourceClass} must use central RBAC.",
            );

            $this->assertContains(
                $resourceClass,
                $registered,
                "{$resourceClass} must be registered.",
            );
        }

        $this->assertCount(
            39,
            $registered,
        );
    }

    public function test_registry_rules_match_inspected_roles_and_exact_actions(): void
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

            $rule =
                $rules[$resourceClass];

            $this->assertSame(
                $contract['roles'],
                $rule['roles'],
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
                    $rule['actions'][$action],
                );
            }
        }
    }

    public function test_current_role_defaults_keep_the_same_operational_access(): void
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

                foreach ($defaults as $role => $permissions) {
                    $expected =
                        in_array(
                            $role,
                            $contract['roles'],
                            true,
                        )
                        && in_array(
                            $permission,
                            $permissions,
                            true,
                        );

                    $this->assertSame(
                        $expected,
                        RbacPermissionRegistry::allowsResourceAction(
                            $this->user(
                                $role,
                                $permissions,
                            ),
                            $resourceClass,
                            $action,
                        ),
                        "{$role} default mismatch for {$resourceClass} {$action}.",
                    );
                }
            }
        }
    }

    public function test_exact_manual_permission_is_still_required_inside_allowed_role_matrix(): void
    {
        foreach ($this->resources() as $resourceClass => $contract) {
            $allowedRole =
                $contract['roles'][0];

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
                            $allowedRole,
                            [$permission],
                        ),
                        $resourceClass,
                        $action,
                    ),
                );

                $this->assertFalse(
                    RbacPermissionRegistry::allowsResourceAction(
                        $this->user(
                            $allowedRole,
                            [],
                        ),
                        $resourceClass,
                        $action,
                    ),
                );
            }
        }
    }

    public function test_disallowed_roles_and_inactive_users_cannot_bypass_exact_permission(): void
    {
        foreach ($this->resources() as $resourceClass => $contract) {
            $permission =
                $contract['prefix']
                .'_view';

            $disallowed =
                array_values(
                    array_diff(
                        [
                            RbacPermissionRegistry::ROLE_MANAGER,
                            RbacPermissionRegistry::ROLE_ACCOUNTANT,
                            RbacPermissionRegistry::ROLE_SALES_STAFF,
                        ],
                        $contract['roles'],
                    ),
                );

            foreach ($disallowed as $role) {
                $this->assertFalse(
                    RbacPermissionRegistry::allowsResourceAction(
                        $this->user(
                            $role,
                            [$permission],
                        ),
                        $resourceClass,
                        'view',
                    ),
                );
            }

            $inactive =
                $this->user(
                    $contract['roles'][0],
                    [$permission],
                );

            $inactive->is_active =
                false;

            $this->assertFalse(
                RbacPermissionRegistry::allowsResourceAction(
                    $inactive,
                    $resourceClass,
                    'view',
                ),
            );

            $this->assertTrue(
                RbacPermissionRegistry::allowsResourceAction(
                    $this->user(
                        RbacPermissionRegistry::ROLE_ADMIN,
                        [],
                    ),
                    $resourceClass,
                    'view',
                ),
            );
        }
    }

    public function test_legacy_resource_authorization_source_methods_were_removed(): void
    {
        foreach ($this->resources() as $resourceClass => $contract) {
            $reflection =
                new ReflectionClass(
                    $resourceClass,
                );

            $path =
                $reflection->getFileName();

            $this->assertIsString(
                $path,
            );

            $source =
                file_get_contents($path);

            $this->assertIsString(
                $source,
            );

            $this->assertStringContainsString(
                'HasRbacResourceAuthorization',
                $source,
            );

            foreach (
                [
                    'canAccess',
                    'canCreate',
                    'canEdit',
                    'canDelete',
                    'canDeleteAny',
                ]
                as $method
            ) {
                $this->assertDoesNotMatchRegularExpression(
                    '/public\s+static\s+function\s+'
                    .preg_quote($method, '/')
                    .'\s*\(/',
                    $source,
                    "{$resourceClass} still declares legacy {$method} in its source file.",
                );
            }
        }
    }

    /**
     * @return array<class-string, array{
     *     prefix: string,
     *     roles: list<string>
     * }>
     */
    private function resources(): array
    {
        return [
            AirlineResource::class => [
                'prefix' =>
                    'airlines',
                'roles' => [
                    RbacPermissionRegistry::ROLE_MANAGER,
                    RbacPermissionRegistry::ROLE_SALES_STAFF,
                ],
            ],
            AirportResource::class => [
                'prefix' =>
                    'airports',
                'roles' => [
                    RbacPermissionRegistry::ROLE_MANAGER,
                    RbacPermissionRegistry::ROLE_SALES_STAFF,
                ],
            ],
            CityResource::class => [
                'prefix' =>
                    'cities',
                'roles' => [
                    RbacPermissionRegistry::ROLE_MANAGER,
                    RbacPermissionRegistry::ROLE_SALES_STAFF,
                ],
            ],
            CountryResource::class => [
                'prefix' =>
                    'countries',
                'roles' => [
                    RbacPermissionRegistry::ROLE_MANAGER,
                    RbacPermissionRegistry::ROLE_SALES_STAFF,
                ],
            ],
            FlightBookingResource::class => [
                'prefix' =>
                    'flight_bookings',
                'roles' => [
                    RbacPermissionRegistry::ROLE_MANAGER,
                    RbacPermissionRegistry::ROLE_ACCOUNTANT,
                    RbacPermissionRegistry::ROLE_SALES_STAFF,
                ],
            ],
            FlightPaymentResource::class => [
                'prefix' =>
                    'flight_payments',
                'roles' => [
                    RbacPermissionRegistry::ROLE_MANAGER,
                    RbacPermissionRegistry::ROLE_ACCOUNTANT,
                    RbacPermissionRegistry::ROLE_SALES_STAFF,
                ],
            ],
            VisaApplicationResource::class => [
                'prefix' =>
                    'visa_applications',
                'roles' => [
                    RbacPermissionRegistry::ROLE_MANAGER,
                    RbacPermissionRegistry::ROLE_ACCOUNTANT,
                    RbacPermissionRegistry::ROLE_SALES_STAFF,
                ],
            ],
            VisaTypeResource::class => [
                'prefix' =>
                    'visa_types',
                'roles' => [
                    RbacPermissionRegistry::ROLE_MANAGER,
                    RbacPermissionRegistry::ROLE_SALES_STAFF,
                ],
            ],
            UmrahBookingResource::class => [
                'prefix' =>
                    'umrah_bookings',
                'roles' => [
                    RbacPermissionRegistry::ROLE_MANAGER,
                    RbacPermissionRegistry::ROLE_ACCOUNTANT,
                    RbacPermissionRegistry::ROLE_SALES_STAFF,
                ],
            ],
            UmrahPaymentResource::class => [
                'prefix' =>
                    'umrah_payments',
                'roles' => [
                    RbacPermissionRegistry::ROLE_MANAGER,
                    RbacPermissionRegistry::ROLE_ACCOUNTANT,
                    RbacPermissionRegistry::ROLE_SALES_STAFF,
                ],
            ],
            BlogPostResource::class => [
                'prefix' =>
                    'blog_posts',
                'roles' => [
                    RbacPermissionRegistry::ROLE_MANAGER,
                ],
            ],
            FaqResource::class => [
                'prefix' =>
                    'faqs',
                'roles' => [
                    RbacPermissionRegistry::ROLE_MANAGER,
                ],
            ],
            GalleryItemResource::class => [
                'prefix' =>
                    'gallery',
                'roles' => [
                    RbacPermissionRegistry::ROLE_MANAGER,
                ],
            ],
            PakistanTourResource::class => [
                'prefix' =>
                    'pakistan_tours',
                'roles' => [
                    RbacPermissionRegistry::ROLE_MANAGER,
                ],
            ],
            PromotionResource::class => [
                'prefix' =>
                    'promotions',
                'roles' => [
                    RbacPermissionRegistry::ROLE_MANAGER,
                ],
            ],
            TestimonialResource::class => [
                'prefix' =>
                    'testimonials',
                'roles' => [
                    RbacPermissionRegistry::ROLE_MANAGER,
                ],
            ],
        ];
    }

    /**
     * @return list<class-string>
     */
    private function recursiveClassUses(
        string $class,
    ): array {
        $results = [];

        do {
            $results +=
                class_uses($class)
                ?: [];
        } while (
            $class =
                get_parent_class($class)
        );

        foreach ($results as $trait) {
            $results +=
                $this->recursiveClassUses(
                    $trait,
                );
        }

        return array_values(
            array_unique($results),
        );
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