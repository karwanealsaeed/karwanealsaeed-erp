<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class KarwanAgentPortalSharedShellTest extends TestCase
{
    private const PRESERVED_VIEW_HASHES = [
        'resources/views/customer-portal/booking-tracking.blade.php' => '89467d60414168afbab82165d6b24093e8976a2d31ea049f046a148c31a7efd1',
        'resources/views/customer-portal/dashboard.blade.php' => '011b46159dec00eadbe6ad75e2c8c0ccb2ca80869c680afa064431e06ee8ae2c',
        'resources/views/customer-portal/documents.blade.php' => 'e3c501b41e12f4caf7d189ecc7eb4d8783d1ad5c6c92dba0e7107dfc737779c2',
        'resources/views/customer-portal/family-members.blade.php' => '328c04b97e3766d8faf313da8ba064f2e09652a0d41f1ec3319fde1d2d1d8f6f',
        'resources/views/customer-portal/ledger.blade.php' => '403fa444ea8f22e64c17d35c649f158706a95e332b1a93fc82111789e3fd7905',
        'resources/views/customer-portal/list.blade.php' => '0bac4367cb3312f0996d87344690fe50b741ff5e7a1a8d91881d3bbda5c4c506',
        'resources/views/customer-portal/login.blade.php' => '19707a17b9ecd251b1adc642bc35dcc52330911d3cb90195e3bbdb284242cc4b',
        'resources/views/customer-portal/notifications.blade.php' => 'b499d6196d42eafb0e0c5a57151180c31d26e8d71e2e8fc73faf6934c6f2a8a4',
        'resources/views/customer-portal/partials/pagination.blade.php' => '39971bbf4b67985883aca1ae134ad7227a4dfeed8958bbf018073fbe6fc59852',
        'resources/views/customer-portal/payment-uploads.blade.php' => '95c630884ffca1f8ec9e873b8b7254b47e45fc1d86e82fd24ba8890a8b60a6d1',
        'resources/views/customer-portal/profile.blade.php' => '1396d6b99952afb40cd9fcc4fc7bb8a1d3636d9a15b25fd413a52b8dcaa3d95f',
        'resources/views/customer-portal/promotions.blade.php' => 'c93282168781f0c014084d5a9b984b2d0728c07db2be5e3982839eb22f67d6fa',
        'resources/views/customer-portal/support-ticket-show.blade.php' => '75cf3aad320acf01119cb79a71df72555853b0f2ecbc82c720e3f1ba4cf7653c',
        'resources/views/customer-portal/support-tickets.blade.php' => 'bb48432c39a5a2b08d0a28b07cd7d9a07dc70a6ab5f6284d3905bc01fd087e51',
        'resources/views/customer-portal/travel-insurance.blade.php' => '41f8cb5efb64493ea6d84bd69e688c89f2826e6722ad4d4aa3fedfcc37e32286',
        'resources/views/customer-portal/visa-applications.blade.php' => '223aac35c39b901666e10d95d9f66e729efd956c29a124650c1115e956b4de08',
    ];

    public function test_agent_layout_has_v3_shared_shell_contract(): void
    {
        $contents = file_get_contents(
            resource_path(
                'views/customer-portal/layout.blade.php',
            ),
        );

        $this->assertIsString($contents);

        foreach (
            [
                'PHASE 16E-2A AGENT PORTAL SHARED SHELL START',
                'data-kes-agent-portal="v3"',
                'class="cp-body kes-agent-portal"',
                "app()->isLocale('ur') ? 'rtl' : 'ltr'",
                'css/agent-portal-v3.css',
                'js/agent-portal-v3.js',
                'data-agent-nav-toggle',
                'data-agent-nav',
                'id="agent-portal-navigation"',
                'aria-controls="customerPortalSidebar"',
                'id="customerPortalSidebar"',
                'aria-expanded="false"',
                'Agent portal navigation',
                'kes-agent-skip-link',
                'id="agent-portal-main"',
                'class="cp-backdrop kes-agent-nav-backdrop"',
            ] as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $contents,
            );
        }

        $this->assertStringNotContainsString(
            'Customer portal navigation',
            $contents,
        );
        $this->assertStringContainsString(
            'Agent Portal',
            $contents,
        );
        $this->assertStringNotContainsString(
            "document.body.classList.toggle('cp-menu-open')",
            $contents,
        );
        $this->assertStringNotContainsString(
            'onclick="document.body.classList.remove',
            $contents,
        );
    }

    public function test_register_uses_agent_portal_label_only(): void
    {
        $contents = file_get_contents(
            resource_path(
                'views/customer-portal/register.blade.php',
            ),
        );

        $this->assertIsString($contents);
        $this->assertStringContainsString(
            '<span>Agent Portal</span>',
            $contents,
        );
        $this->assertStringNotContainsString(
            '<span>Customer Portal</span>',
            $contents,
        );
    }

    public function test_agent_css_has_responsive_rtl_accessible_foundation(): void
    {
        $contents = file_get_contents(
            public_path('css/agent-portal-v3.css'),
        );

        $this->assertIsString($contents);

        foreach (
            [
                'PHASE 16E-2A AGENT PORTAL SHARED SHELL START',
                '--kes-agent-gold',
                '.kes-agent-portal .cp-nav',
                '.kes-agent-portal .cp-sidebar',
                'min-height: 44px',
                '@media (max-width: 960px)',
                'kes-agent-nav-open',
                'html[dir="rtl"]',
                'overflow-x: auto',
                'prefers-reduced-motion',
                '.kes-agent-skip-link',
                ':focus-visible',
            ] as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $contents,
            );
        }
    }

    public function test_agent_javascript_is_progressive_and_keyboard_safe(): void
    {
        $contents = file_get_contents(
            public_path('js/agent-portal-v3.js'),
        );

        $this->assertIsString($contents);

        foreach (
            [
                'data-kes-agent-portal="v3"',
                'data-agent-nav-toggle',
                'data-agent-nav',
                'aria-expanded',
                "event.key === 'Escape'",
                'matchMedia',
                'requestAnimationFrame',
                'kes-agent-nav-lock',
                'cp-menu-open',
            ] as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $contents,
            );
        }

        $this->assertStringNotContainsString(
            'eval(',
            $contents,
        );
        $this->assertStringNotContainsString(
            'innerHTML',
            $contents,
        );
    }

    public function test_all_agent_portal_route_contracts_are_preserved(): void
    {
        $routes = collect(
            Route::getRoutes()->getRoutes(),
        )
            ->filter(
                static fn ($route): bool => str_starts_with(
                    (string) $route->getName(),
                    'customer.portal.',
                ),
            )
            ->values();

        $this->assertCount(36, $routes);

        foreach (
            [
                'customer.portal.login',
                'customer.portal.authenticate',
                'customer.portal.register',
                'customer.portal.register.store',
                'customer.portal.dashboard',
                'customer.portal.tracking',
                'customer.portal.flights',
                'customer.portal.hotels',
                'customer.portal.umrah',
                'customer.portal.visa',
                'customer.portal.invoices',
                'customer.portal.vouchers',
                'customer.portal.ledger',
                'customer.portal.documents',
                'customer.portal.documents.download',
                'customer.portal.payment_uploads',
                'customer.portal.payment_uploads.store',
                'customer.portal.payment_uploads.receipt',
                'customer.portal.family',
                'customer.portal.family.store',
                'customer.portal.family.delete',
                'customer.portal.notifications',
                'customer.portal.notifications.read',
                'customer.portal.notifications.mark_all_read',
                'customer.portal.support',
                'customer.portal.support.store',
                'customer.portal.support.show',
                'customer.portal.support.reply',
                'customer.portal.promotions',
                'customer.portal.travel_insurance',
                'customer.portal.travel_insurance.policy_document',
                'customer.portal.travel_insurance.payment_receipt',
                'customer.portal.profile',
                'customer.portal.profile.update',
                'customer.portal.profile.password',
                'customer.portal.logout',
            ] as $routeName
        ) {
            $this->assertTrue(
                Route::has($routeName),
                'Missing Agent Portal route: '
                .$routeName,
            );
        }
    }

    public function test_all_unmodified_agent_views_keep_exact_hashes(): void
    {
        foreach (
            self::PRESERVED_VIEW_HASHES as $relative => $expectedHash
        ) {
            $contents = file_get_contents(
                base_path($relative),
            );

            $this->assertIsString($contents);
            $this->assertSame(
                $expectedHash,
                hash('sha256', $contents),
                'Unexpected Agent Portal view change: '
                .$relative,
            );
        }
    }

    public function test_guest_and_agent_auth_boundaries_remain_intact(): void
    {
        $this->get(
            route('customer.portal.login'),
        )->assertOk();

        $this->get(
            route('customer.portal.register'),
        )
            ->assertRedirect(
                route('customer.portal.login'),
            )
            ->assertSessionHas('error');

        $this->get(
            route('customer.portal.dashboard'),
        )->assertRedirect(
            route('customer.portal.login'),
        );
    }
}
