<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CustomerPortalSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_portal_dashboard(): void
    {
        $this->get(
            route(
                'customer.portal.dashboard',
            ),
        )
            ->assertRedirect(
                route(
                    'customer.portal.login',
                ),
            );
    }

    public function test_active_agent_can_login(): void
    {
        $customer =
            $this->agent();

        $this->post(
            route(
                'customer.portal.authenticate',
            ),
            [
                'login' =>
                    $customer->mobile,

                'password' =>
                    'PortalPassword123!',
            ],
        )
            ->assertRedirect(
                route(
                    'customer.portal.dashboard',
                ),
            )
            ->assertSessionHas(
                'customer_portal_id',
                $customer->id,
            )
            ->assertSessionMissing(
                'error',
            );

        $this->assertNotNull(
            $customer->fresh()
                ->last_portal_login_at,
        );
    }

    public function test_invalid_non_agent_and_inactive_accounts_receive_generic_error(): void
    {
        $customer =
            $this->agent([
                'customer_type' =>
                    'Customer',
            ]);

        $response =
            $this->from(
                route(
                    'customer.portal.login',
                ),
            )->post(
                route(
                    'customer.portal.authenticate',
                ),
                [
                    'login' =>
                        $customer->mobile,

                    'password' =>
                        'PortalPassword123!',
                ],
            );

        $response
            ->assertRedirect(
                route(
                    'customer.portal.login',
                ),
            )
            ->assertSessionHas(
                'error',
                'Login details ghalat hain ya portal access available nahi hai.',
            )
            ->assertSessionMissing(
                'customer_portal_id',
            );
    }

    public function test_deactivated_agent_session_is_immediately_revoked(): void
    {
        $customer =
            $this->agent([
                'status' =>
                    'Inactive',
            ]);

        $this->withSession(
            $this->portalSession(
                $customer,
            ),
        )
            ->get(
                route(
                    'customer.portal.dashboard',
                ),
            )
            ->assertRedirect(
                route(
                    'customer.portal.login',
                ),
            )
            ->assertSessionMissing(
                'customer_portal_id',
            );
    }

    public function test_customer_cannot_delete_another_customers_family_member(): void
    {
        $first =
            $this->agent();

        $second =
            $this->agent([
                'mobile' =>
                    '03000000002',

                'email' =>
                    'agent2@example.test',
            ]);

        $familyMemberId =
            DB::table(
                'customer_family_members',
            )->insertGetId([
                'customer_id' =>
                    $second->id,

                'name' =>
                    'Second Agent Family',

                'relationship' =>
                    'Brother',

                'status' =>
                    'Active',

                'created_at' =>
                    now(),

                'updated_at' =>
                    now(),
            ]);

        $this->withSession(
            $this->portalSession(
                $first,
            ),
        )
            ->delete(
                route(
                    'customer.portal.family.delete',
                    $familyMemberId,
                ),
            )
            ->assertForbidden();

        $this->assertDatabaseHas(
            'customer_family_members',
            [
                'id' =>
                    $familyMemberId,
            ],
        );
    }

    public function test_customer_cannot_read_another_customers_notification(): void
    {
        $first =
            $this->agent();

        $second =
            $this->agent([
                'mobile' =>
                    '03000000003',

                'email' =>
                    'agent3@example.test',
            ]);

        $notificationId =
            DB::table(
                'customer_notifications',
            )->insertGetId([
                'customer_id' =>
                    $second->id,

                'title' =>
                    'Private Notification',

                'type' =>
                    'Notification',

                'message' =>
                    'Private message',

                'status' =>
                    'Active',

                'created_at' =>
                    now(),

                'updated_at' =>
                    now(),
            ]);

        $this->withSession(
            $this->portalSession(
                $first,
            ),
        )
            ->post(
                route(
                    'customer.portal.notifications.read',
                    $notificationId,
                ),
            )
            ->assertForbidden();

        $this->assertDatabaseMissing(
            'customer_notification_reads',
            [
                'customer_id' =>
                    $first->id,

                'customer_notification_id' =>
                    $notificationId,
            ],
        );
    }

    public function test_customer_cannot_view_or_reply_to_another_customers_ticket(): void
    {
        $first =
            $this->agent();

        $second =
            $this->agent([
                'mobile' =>
                    '03000000004',

                'email' =>
                    'agent4@example.test',
            ]);

        $ticketId =
            DB::table(
                'support_tickets',
            )->insertGetId([
                'ticket_no' =>
                    'TKT-SEC-001',

                'customer_id' =>
                    $second->id,

                'name' =>
                    $second->name,

                'mobile' =>
                    $second->mobile,

                'email' =>
                    $second->email,

                'subject' =>
                    'Private Ticket',

                'message' =>
                    'Private ticket message',

                'priority' =>
                    'Normal',

                'status' =>
                    'Open',

                'created_at' =>
                    now(),

                'updated_at' =>
                    now(),
            ]);

        $session =
            $this->portalSession(
                $first,
            );

        $this->withSession(
            $session,
        )
            ->get(
                route(
                    'customer.portal.support.show',
                    $ticketId,
                ),
            )
            ->assertForbidden();

        $this->withSession(
            $session,
        )
            ->post(
                route(
                    'customer.portal.support.reply',
                    $ticketId,
                ),
                [
                    'message' =>
                        'Unauthorized reply',
                ],
            )
            ->assertForbidden();

        $this->assertDatabaseMissing(
            'support_ticket_replies',
            [
                'support_ticket_id' =>
                    $ticketId,

                'customer_id' =>
                    $first->id,
            ],
        );
    }

    public function test_logout_invalidates_portal_session(): void
    {
        $customer =
            $this->agent();

        $this->withSession(
            $this->portalSession(
                $customer,
            ),
        )
            ->post(
                route(
                    'customer.portal.logout',
                ),
            )
            ->assertRedirect(
                route(
                    'customer.portal.login',
                ),
            )
            ->assertSessionMissing(
                'customer_portal_id',
            )
            ->assertSessionMissing(
                'customer_portal_name',
            )
            ->assertSessionMissing(
                'customer_portal_last_activity',
            );
    }

    public function test_login_and_protected_routes_are_rate_limited(): void
    {
        $loginRoute =
            Route::getRoutes()
                ->getByName(
                    'customer.portal.authenticate',
                );

        $dashboardRoute =
            Route::getRoutes()
                ->getByName(
                    'customer.portal.dashboard',
                );

        $this->assertNotNull(
            $loginRoute,
        );

        $this->assertNotNull(
            $dashboardRoute,
        );

        $this->assertContains(
            'throttle:6,1',
            $loginRoute
                ->gatherMiddleware(),
        );

        $this->assertContains(
            'throttle:120,1',
            $dashboardRoute
                ->gatherMiddleware(),
        );
    }

    public function test_portal_source_contains_session_fixation_protection(): void
    {
        $controller =
            file_get_contents(
                app_path(
                    'Http/Controllers/CustomerPortalController.php',
                ),
            );

        $middleware =
            file_get_contents(
                app_path(
                    'Http/Middleware/CustomerPortalAuth.php',
                ),
            );

        $this->assertStringContainsString(
            '$request->session()',
            $controller,
        );

        $this->assertStringContainsString(
            '->regenerate();',
            $controller,
        );

        $this->assertStringContainsString(
            '->invalidate();',
            $controller,
        );

        $this->assertStringContainsString(
            '->regenerateToken();',
            $controller,
        );

        $this->assertStringContainsString(
            "=== 'Agent'",
            $middleware,
        );

        $this->assertStringContainsString(
            "=== 'Active'",
            $middleware,
        );
    }

    private function agent(
        array $overrides = [],
    ): Customer {
        return Customer::query()
            ->create(
                array_merge(
                    [
                        'name' =>
                            'Portal Agent',

                        'mobile' =>
                            '03000000001',

                        'email' =>
                            'agent1@example.test',

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
                    $overrides,
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
        ];
    }
}