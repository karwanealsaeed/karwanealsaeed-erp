<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerFamilyMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerPortalPaginationUiTest extends TestCase
{
    use RefreshDatabase;

    private static int $sequence = 0;

    public function test_all_customer_portal_listing_routes_render(): void
    {
        $customer =
            $this->agent();

        $routes = [
            'customer.portal.visa',
            'customer.portal.flights',
            'customer.portal.umrah',
            'customer.portal.hotels',
            'customer.portal.invoices',
            'customer.portal.vouchers',
            'customer.portal.tracking',
            'customer.portal.documents',
            'customer.portal.payment_uploads',
            'customer.portal.family',
            'customer.portal.notifications',
            'customer.portal.support',
        ];

        foreach ($routes as $routeName) {
            $this->withSession(
                $this->portalSession(
                    $customer,
                ),
            )
                ->get(
                    route(
                        $routeName,
                    ),
                )
                ->assertOk();
        }
    }

    public function test_generic_list_route_receives_paginated_view_contract(): void
    {
        $customer =
            $this->agent();

        $this->withSession(
            $this->portalSession(
                $customer,
            ),
        )
            ->get(
                route(
                    'customer.portal.visa',
                ),
            )
            ->assertOk()
            ->assertViewIs(
                'customer-portal.visa-applications',
            )
            ->assertViewHas(
                'visaApplications',
                function (
                    $visaApplications,
                ): bool {
                    return $visaApplications
                        instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator
                        && $visaApplications->getPageName()
                            === 'visa_page';
                },
            );    }

    public function test_booking_tracking_uses_unique_paginator_names(): void
    {
        $customer =
            $this->agent();

        $response =
            $this->withSession(
                $this->portalSession(
                    $customer,
                ),
            )
                ->get(
                    route(
                        'customer.portal.tracking',
                    ),
                );

        $response
            ->assertOk()
            ->assertViewHas(
                'visaApplications',
                fn (
                    mixed $records,
                ): bool =>
                    $records
                    instanceof LengthAwarePaginator
                    && $records->getPageName()
                    === 'visa_page',
            )
            ->assertViewHas(
                'flightBookings',
                fn (
                    mixed $records,
                ): bool =>
                    $records
                    instanceof LengthAwarePaginator
                    && $records->getPageName()
                    === 'flight_page',
            )
            ->assertViewHas(
                'umrahBookings',
                fn (
                    mixed $records,
                ): bool =>
                    $records
                    instanceof LengthAwarePaginator
                    && $records->getPageName()
                    === 'umrah_page',
            )
            ->assertViewHas(
                'hotelBookings',
                fn (
                    mixed $records,
                ): bool =>
                    $records
                    instanceof LengthAwarePaginator
                    && $records->getPageName()
                    === 'hotel_page',
            );
    }

    public function test_large_portal_listing_views_receive_paginators(): void
    {
        $customer =
            $this->agent();

        $cases = [
            [
                'route' =>
                    'customer.portal.documents',

                'variable' =>
                    'documents',

                'per_page' =>
                    15,
            ],

            [
                'route' =>
                    'customer.portal.payment_uploads',

                'variable' =>
                    'uploads',

                'per_page' =>
                    15,
            ],

            [
                'route' =>
                    'customer.portal.family',

                'variable' =>
                    'members',

                'per_page' =>
                    15,
            ],

            [
                'route' =>
                    'customer.portal.notifications',

                'variable' =>
                    'notifications',

                'per_page' =>
                    12,
            ],

            [
                'route' =>
                    'customer.portal.support',

                'variable' =>
                    'tickets',

                'per_page' =>
                    15,
            ],
        ];

        foreach ($cases as $case) {
            $this->withSession(
                $this->portalSession(
                    $customer,
                ),
            )
                ->get(
                    route(
                        $case['route'],
                    ),
                )
                ->assertOk()
                ->assertViewHas(
                    $case['variable'],
                    fn (
                        mixed $records,
                    ): bool =>
                        $records
                        instanceof LengthAwarePaginator
                        && $records->perPage()
                        === $case['per_page'],
                );
        }
    }

    public function test_family_listing_is_paginated_and_customer_scoped(): void
    {
        $customer =
            $this->agent();

        $otherCustomer =
            $this->agent();

        CustomerFamilyMember::query()
            ->create([
                'customer_id' =>
                    $otherCustomer->id,

                'name' =>
                    'Other Customer Secret Member',

                'relationship' =>
                    'Sibling',

                'status' =>
                    'Active',
            ]);

        foreach (
            range(
                1,
                18,
            )
            as $index
        ) {
            CustomerFamilyMember::query()
                ->create([
                    'customer_id' =>
                        $customer->id,

                    'name' =>
                        'Portal Family Member '
                        .$index,

                    'relationship' =>
                        'Relative',

                    'status' =>
                        'Active',
                ]);
        }

        $this->withSession(
            $this->portalSession(
                $customer,
            ),
        )
            ->get(
                route(
                    'customer.portal.family',
                    [
                        'page' =>
                            2,
                    ],
                ),
            )
            ->assertOk()
            ->assertDontSee(
                'Other Customer Secret Member',
            )
            ->assertViewHas(
                'members',
                fn (
                    mixed $members,
                ): bool =>
                    $members
                    instanceof LengthAwarePaginator
                    && $members->total()
                    === 18
                    && $members->currentPage()
                    === 2
                    && $members->count()
                    === 3,
            );
    }

    public function test_notification_view_rejects_unsafe_action_url_scheme(): void
    {
        $customer =
            $this->agent();

        DB::table(
            'customer_notifications',
        )->insert([
            'customer_id' =>
                $customer->id,

            'title' =>
                'Unsafe Action Test',

            'type' =>
                'Alert',

            'message' =>
                'Unsafe URL must never become a link.',

            'action_label' =>
                'Unsafe Link',

            'action_url' =>
                'javascript:alert(1)',

            'is_read' =>
                false,

            'status' =>
                'Active',

            'created_at' =>
                now(),

            'updated_at' =>
                now(),
        ]);

        $this->withSession(
            $this->portalSession(
                $customer,
            ),
        )
            ->get(
                route(
                    'customer.portal.notifications',
                ),
            )
            ->assertOk()
            ->assertSee(
                'Unsafe Action Test',
            )
            ->assertDontSee(
                'javascript:alert(1)',
                false,
            )
            ->assertDontSee(
                '>Unsafe Link</a>',
                false,
            );
    }

    public function test_portal_views_include_accessibility_and_link_security(): void
    {
        $family =
            file_get_contents(
                resource_path(
                    'views/customer-portal/family-members.blade.php',
                ),
            );

        $support =
            file_get_contents(
                resource_path(
                    'views/customer-portal/support-tickets.blade.php',
                ),
            );

        $layout =
            file_get_contents(
                resource_path(
                    'views/customer-portal/layout.blade.php',
                ),
            );

        $notifications =
            file_get_contents(
                resource_path(
                    'views/customer-portal/notifications.blade.php',
                ),
            );

        $pagination =
            file_get_contents(
                resource_path(
                    'views/customer-portal/partials/pagination.blade.php',
                ),
            );

        $css =
            file_get_contents(
                public_path(
                    'css/customer-portal.css',
                ),
            );

        $this->assertStringContainsString(
            'label for="family-name"',
            $family,
        );

        $this->assertStringContainsString(
            'label for="support-subject"',
            $support,
        );

        $this->assertStringContainsString(
            'scope="col"',
            $support,
        );

        $this->assertStringContainsString(
            'aria-label="Customer portal navigation"',
            $layout,
        );

        $this->assertStringContainsString(
            'rel="noopener noreferrer"',
            $layout,
        );

        $this->assertStringContainsString(
            '$hasSafeActionUrl',
            $notifications,
        );

        $this->assertStringContainsString(
            'aria-current="page"',
            $pagination,
        );

        $this->assertStringContainsString(
            'CUSTOMER PORTAL PHASE 4B START',
            $css,
        );

        $this->assertStringContainsString(
            ':focus-visible',
            $css,
        );
    }

    public function test_portal_controller_uses_bounded_listing_queries(): void
    {
        $source =
            file_get_contents(
                app_path(
                    'Http/Controllers/CustomerPortalController.php',
                ),
            );

        $this->assertStringContainsString(
            'private function rowsFromTable(',
            $source,
        );

        $this->assertStringContainsString(
            '): LengthAwarePaginator',
            $source,
        );

        $this->assertGreaterThanOrEqual(
            6,
            substr_count(
                $source,
                '->paginate(',
            ),
        );

        $this->assertStringContainsString(
            '->lazyById(',
            $source,
        );

        $this->assertStringContainsString(
            "'visa_page'",
            $source,
        );

        $this->assertStringContainsString(
            "'hotel_page'",
            $source,
        );
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

        return Customer::query()
            ->create([
                'name' =>
                    'Pagination Agent '
                    .$sequence,

                'mobile' =>
                    '0350000'
                    .$sequence,

                'email' =>
                    'pagination-agent-'
                    .$sequence
                    .'@example.test',

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
            ]);
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
                    (string) $customer
                        ->portal_password,
                ),
        ];
    }
}
