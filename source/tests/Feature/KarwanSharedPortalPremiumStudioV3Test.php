<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class KarwanSharedPortalPremiumStudioV3Test extends TestCase
{
    private const PUBLIC_THEME_HASH =
        'a65028378057422211161435ad97935cdde20f445c33f611095578d33e4a2ea1';

    private const SHARED_UI_HASH =
        'f1b713b78ce43cc276d6c025131eeef52354e75e6e385fb0722c1b14d8332c33';

    private const AGENT_THEME_HASH =
        '76b53c51c782a94c8df94075fa9528645f178e6b08fbd7e1b37a8dc59e0b5818';

    private const AGENT_DASHBOARD_HASH =
        '011b46159dec00eadbe6ad75e2c8c0ccb2ca80869c680afa064431e06ee8ae2c';

    private const ADMIN_PANEL_PROVIDER_HASH =
        '10a9ed77cf8a86ee1141ba518e58f0456caf0feeb9c8ab6400734ba328629471';

    private const EXPECTED_AGENT_ROUTES =
        [
            0 => 'customer.portal.authenticate',
            1 => 'customer.portal.dashboard',
            2 => 'customer.portal.documents',
            3 => 'customer.portal.documents.download',
            4 => 'customer.portal.family',
            5 => 'customer.portal.family.delete',
            6 => 'customer.portal.family.store',
            7 => 'customer.portal.flights',
            8 => 'customer.portal.hotels',
            9 => 'customer.portal.invoices',
            10 => 'customer.portal.ledger',
            11 => 'customer.portal.login',
            12 => 'customer.portal.logout',
            13 => 'customer.portal.notifications',
            14 => 'customer.portal.notifications.mark_all_read',
            15 => 'customer.portal.notifications.read',
            16 => 'customer.portal.payment_uploads',
            17 => 'customer.portal.payment_uploads.receipt',
            18 => 'customer.portal.payment_uploads.store',
            19 => 'customer.portal.profile',
            20 => 'customer.portal.profile.password',
            21 => 'customer.portal.profile.update',
            22 => 'customer.portal.promotions',
            23 => 'customer.portal.register',
            24 => 'customer.portal.register.store',
            25 => 'customer.portal.support',
            26 => 'customer.portal.support.reply',
            27 => 'customer.portal.support.show',
            28 => 'customer.portal.support.store',
            29 => 'customer.portal.tracking',
            30 => 'customer.portal.travel_insurance',
            31 => 'customer.portal.travel_insurance.payment_receipt',
            32 => 'customer.portal.travel_insurance.policy_document',
            33 => 'customer.portal.umrah',
            34 => 'customer.portal.visa',
            35 => 'customer.portal.vouchers',
        ];

    public function test_shared_theme_redeclares_exact_public_v3_tokens(): void
    {
        $css = file_get_contents(
            public_path(
                'css/karwan-portal-premium-studio-v3.css',
            ),
        );

        $this->assertIsString($css);

        foreach (
            [
                '--body: "Manrope","Noto Naskh Arabic",system-ui,sans-serif;',
                '--charcoal: #211D1C;',
                '--cream: #FEF8D4;',
                '--display: "Cormorant Garamond","Noto Naskh Arabic",Georgia,serif;',
                '--ease: cubic-bezier(.22,1,.36,1);',
                '--gold: #F9CB20;',
                '--green: #062D24;',
                '--green-2: #0A3D31;',
                '--icon-2d-cream: #FEF8D4;',
                '--icon-2d-gold: #F9CB20;',
                '--icon-2d-green: #0A3D31;',
                '--icon-2d-green-deep: #062D24;',
                '--icon-2d-line: rgba(179,92,34,.18);',
                '--icon-2d-rust: #B35C22;',
                '--ink: #211D1C;',
                '--line: rgba(179,92,34,.16);',
                '--line-dark: rgba(255,255,255,.12);',
                '--muted: #746963;',
                '--red: #EA212B;',
                '--rust: #B35C22;',
                '--shadow-lg: 0 42px 120px rgba(7,22,17,.3);',
                '--shadow-md: 0 24px 70px rgba(33,29,28,.12);',
                '--shadow-sm: 0 10px 30px rgba(33,29,28,.07);',
                '--surface: #FFFDF7;',
                '--surface-2: #F7F1E5;',
                '--white: #fff;',
            ] as $token
        ) {
            $this->assertStringContainsString(
                $token,
                $css,
            );
        }
    }

    public function test_agent_and_admin_load_shared_theme_last(): void
    {
        $agentLayout = file_get_contents(
            resource_path(
                'views/customer-portal/layout.blade.php',
            ),
        );
        $adminHead = file_get_contents(
            resource_path(
                'views/filament/admin/theme-head.blade.php',
            ),
        );

        $this->assertIsString($agentLayout);
        $this->assertIsString($adminHead);

        $sharedAssetCall =
            "asset('css/karwan-portal-premium-studio-v3.css')";
        $sharedPhaseStart =
            'PHASE 16E-3F4 SHARED PUBLIC V3 PORTAL FOUNDATION START';

        $this->assertSame(
            1,
            substr_count(
                $agentLayout,
                $sharedAssetCall,
            ),
        );
        $this->assertSame(
            1,
            substr_count(
                $adminHead,
                $sharedAssetCall,
            ),
        );
        $this->assertSame(
            1,
            substr_count(
                $agentLayout,
                $sharedPhaseStart,
            ),
        );
        $this->assertSame(
            1,
            substr_count(
                $adminHead,
                $sharedPhaseStart,
            ),
        );

        $this->assertGreaterThan(
            strpos(
                $agentLayout,
                'agent-portal-v3.css',
            ),
            strpos(
                $agentLayout,
                $sharedAssetCall,
            ),
        );
        $this->assertGreaterThan(
            strpos(
                $adminHead,
                'karwan-premium-ui.css',
            ),
            strpos(
                $adminHead,
                $sharedAssetCall,
            ),
        );
    }

    public function test_shared_theme_covers_agent_admin_responsive_rtl_and_accessibility(): void
    {
        $css = file_get_contents(
            public_path(
                'css/karwan-portal-premium-studio-v3.css',
            ),
        );

        $this->assertIsString($css);

        foreach (
            [
                'body.kes-agent-portal',
                '.kes-agent-dashboard-v3',
                '.cp-sidebar',
                '.cp-topbar',
                '.cp-main',
                '.cp-card',
                '.customer-latest-offer-card',
                'body.fi-body',
                '.fi-sidebar',
                '.fi-topbar',
                '.fi-main',
                '.fi-section',
                '.fi-ta-ctn',
                '.fi-fo-field-wrp',
                '.fi-modal-window',
                '.fi-btn',
                'html[dir="rtl"]',
                '@media (max-width: 1180px)',
                '@media (max-width: 760px)',
                '@media (prefers-reduced-motion: reduce)',
                ':focus-visible',
            ] as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $css,
            );
        }
    }

    public function test_routes_and_agent_contracts_are_unchanged(): void
    {
        $routes = Route::getRoutes();
        $namedRoutes = [];
        $agentRoutes = [];

        foreach ($routes as $route) {
            $name = (string) ($route->getName() ?? '');

            if ($name !== '') {
                $namedRoutes[] = $name;
            }

            if (str_starts_with($name, 'customer.portal.')) {
                $agentRoutes[] = $name;
            }
        }

        sort($agentRoutes);
        $expectedAgentRoutes =
            self::EXPECTED_AGENT_ROUTES;
        sort($expectedAgentRoutes);

        $this->assertCount(430, $routes);
        $this->assertCount(36, $agentRoutes);
        $this->assertSame(
            $expectedAgentRoutes,
            $agentRoutes,
            'Agent Portal named-route inventory changed.',
        );

        foreach (
            [
                'website.home',
                'filament.admin.auth.login',
            ] as $routeName
        ) {
            $this->assertContains(
                $routeName,
                $namedRoutes,
                'Missing route: '.$routeName,
            );
        }
    }

    public function test_non_visual_dependencies_remain_byte_exact(): void
    {
        $hashes = [
            public_path('css/karwan-premium-studio-v3.css') => self::PUBLIC_THEME_HASH,
            public_path('css/karwan-premium-ui.css') => self::SHARED_UI_HASH,
            public_path('css/agent-portal-v3.css') => self::AGENT_THEME_HASH,
            resource_path(
                'views/customer-portal/dashboard.blade.php',
            ) => self::AGENT_DASHBOARD_HASH,
            app_path(
                'Providers/Filament/AdminPanelProvider.php',
            ) => self::ADMIN_PANEL_PROVIDER_HASH,
        ];

        foreach ($hashes as $path => $expectedHash) {
            $this->assertSame(
                $expectedHash,
                hash_file('sha256', $path),
                'Unexpected dependency change: '.$path,
            );
        }
    }
}
