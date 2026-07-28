<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class KarwanAgentDashboardV3Test extends TestCase
{
    private const CONTROLLER_HASH =
        'a4120fda88680d494d31a2264c7c3b82913f753d6b8203c07b4d1c54aa6c4cb4';

    private const LATEST_OFFERS_COMPONENT_HASH =
        '8a10da512f92be96769c7840832b081d10d2dc4017f533669438257aa7748d8c';

    private const PRESERVED_VIEW_HASHES =
        [
            'resources/views/customer-portal/booking-tracking.blade.php' => '89467d60414168afbab82165d6b24093e8976a2d31ea049f046a148c31a7efd1',
            'resources/views/customer-portal/documents.blade.php' => 'e3c501b41e12f4caf7d189ecc7eb4d8783d1ad5c6c92dba0e7107dfc737779c2',
            'resources/views/customer-portal/family-members.blade.php' => '328c04b97e3766d8faf313da8ba064f2e09652a0d41f1ec3319fde1d2d1d8f6f',
            'resources/views/customer-portal/layout.blade.php' => '026d712188a6a3cba3cc5fbaddda8379365d410ee50c5dab6f68d7c6d0239619',
            'resources/views/customer-portal/ledger.blade.php' => '403fa444ea8f22e64c17d35c649f158706a95e332b1a93fc82111789e3fd7905',
            'resources/views/customer-portal/list.blade.php' => '0bac4367cb3312f0996d87344690fe50b741ff5e7a1a8d91881d3bbda5c4c506',
            'resources/views/customer-portal/login.blade.php' => '19707a17b9ecd251b1adc642bc35dcc52330911d3cb90195e3bbdb284242cc4b',
            'resources/views/customer-portal/notifications.blade.php' => 'b499d6196d42eafb0e0c5a57151180c31d26e8d71e2e8fc73faf6934c6f2a8a4',
            'resources/views/customer-portal/partials/pagination.blade.php' => '39971bbf4b67985883aca1ae134ad7227a4dfeed8958bbf018073fbe6fc59852',
            'resources/views/customer-portal/payment-uploads.blade.php' => '95c630884ffca1f8ec9e873b8b7254b47e45fc1d86e82fd24ba8890a8b60a6d1',
            'resources/views/customer-portal/profile.blade.php' => '1396d6b99952afb40cd9fcc4fc7bb8a1d3636d9a15b25fd413a52b8dcaa3d95f',
            'resources/views/customer-portal/promotions.blade.php' => 'c93282168781f0c014084d5a9b984b2d0728c07db2be5e3982839eb22f67d6fa',
            'resources/views/customer-portal/register.blade.php' => '16ecb0c32900f1dde890a7c2415052eabe046775777493f1bdcab48d316ff506',
            'resources/views/customer-portal/support-ticket-show.blade.php' => '75cf3aad320acf01119cb79a71df72555853b0f2ecbc82c720e3f1ba4cf7653c',
            'resources/views/customer-portal/support-tickets.blade.php' => 'bb48432c39a5a2b08d0a28b07cd7d9a07dc70a6ab5f6284d3905bc01fd087e51',
            'resources/views/customer-portal/travel-insurance.blade.php' => '41f8cb5efb64493ea6d84bd69e688c89f2826e6722ad4d4aa3fedfcc37e32286',
            'resources/views/customer-portal/visa-applications.blade.php' => '223aac35c39b901666e10d95d9f66e729efd956c29a124650c1115e956b4de08',
        ];

    public function test_dashboard_uses_scoped_premium_v3_contract(): void
    {
        $contents = file_get_contents(
            resource_path(
                'views/customer-portal/dashboard.blade.php',
            ),
        );

        $this->assertIsString($contents);

        foreach (
            [
                'kes-agent-dashboard-v3',
                'kes-agent-dashboard-hero',
                'kes-agent-dashboard-metrics',
                'kes-agent-metric-card',
                'kes-agent-dashboard-actions',
                'kes-agent-action-card',
                'kes-agent-dashboard-offers',
                'Agent Workspace',
                'Secure Agent Portal',
                'onclick="window.print()"',
                '<x-customer-latest-offers />',
            ] as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $contents,
            );
        }

        foreach (
            [
                'cp-page-head',
                'cp-stats-grid',
                'cp-stat-card',
                'cp-card-grid',
                'cp-info-card',
            ] as $legacyClass
        ) {
            $this->assertStringNotContainsString(
                $legacyClass,
                $contents,
            );
        }
    }

    public function test_dashboard_preserves_data_variables_and_routes(): void
    {
        $contents = file_get_contents(
            resource_path(
                'views/customer-portal/dashboard.blade.php',
            ),
        );

        $this->assertIsString($contents);

        foreach (
            [
                '$customer',
                '$ledgerBalance',
                '$notificationsCount',
                '$supportTicketsCount',
                '$paymentUploadsCount',
                '$balance',
            ] as $variable
        ) {
            $this->assertStringContainsString(
                $variable,
                $contents,
            );
        }

        $expectedRoutes = [
            'customer.portal.ledger',
            'customer.portal.tracking',
            'customer.portal.invoices',
            'customer.portal.vouchers',
            'customer.portal.payment_uploads',
            'customer.portal.support',
        ];

        foreach ($expectedRoutes as $routeName) {
            $this->assertTrue(
                Route::has($routeName),
                'Missing Agent route: '.$routeName,
            );
            $this->assertStringContainsString(
                "route('{$routeName}')",
                $contents,
            );
        }

        $actualRoutes = [];

        foreach ($expectedRoutes as $routeName) {
            $needle = "route('{$routeName}')";

            if (str_contains($contents, $needle)) {
                $actualRoutes[] = $routeName;
            }
        }

        $this->assertSame(
            count($expectedRoutes),
            substr_count($contents, 'route('),
            'Unexpected additional dashboard route call detected.',
        );

        sort($actualRoutes);
        sort($expectedRoutes);

        $this->assertSame(
            $expectedRoutes,
            $actualRoutes,
        );
    }

    public function test_dashboard_css_overrides_legacy_visual_conflicts(): void
    {
        $contents = file_get_contents(
            public_path('css/agent-portal-v3.css'),
        );

        $this->assertIsString($contents);

        foreach (
            [
                'PHASE 16E-3D AGENT DASHBOARD V3 START',
                '.kes-agent-dashboard-v3',
                '.kes-agent-dashboard-hero',
                '.kes-agent-dashboard-metrics',
                '.kes-agent-metric-card',
                '.kes-agent-dashboard-actions',
                '.kes-agent-action-card',
                '.kes-agent-dashboard-offers',
                '@media (max-width: 1180px)',
                '@media (max-width: 620px)',
                '@media print',
                'background: #fff !important',
                'prefers-reduced-motion',
            ] as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $contents,
            );
        }
    }

    public function test_dashboard_dependencies_and_other_views_are_unchanged(): void
    {
        $controller = file_get_contents(
            app_path(
                'Http/Controllers/CustomerPortalController.php',
            ),
        );
        $component = file_get_contents(
            resource_path(
                'views/components/customer-latest-offers.blade.php',
            ),
        );

        $this->assertIsString($controller);
        $this->assertIsString($component);

        $this->assertSame(
            self::CONTROLLER_HASH,
            hash('sha256', $controller),
        );
        $this->assertSame(
            self::LATEST_OFFERS_COMPONENT_HASH,
            hash('sha256', $component),
        );

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
                'Unexpected Agent view change: '.$relative,
            );
        }
    }

    public function test_guest_dashboard_boundary_remains_protected(): void
    {
        $this->get(
            route('customer.portal.dashboard'),
        )->assertRedirect(
            route('customer.portal.login'),
        );
    }
}
