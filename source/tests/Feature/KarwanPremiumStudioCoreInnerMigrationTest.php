<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class KarwanPremiumStudioCoreInnerMigrationTest extends TestCase
{
    public function test_controlled_core_views_use_v3_inner_layout(): void
    {
        foreach (
            [
                'page',
                'legal-page',
                'faq',
                'testimonials',
            ] as $view
        ) {
            $contents = file_get_contents(
                resource_path(
                    'views/website/'.$view.'.blade.php',
                ),
            );

            $this->assertIsString($contents);
            $this->assertStringContainsString(
                "@extends('website.inner-v3-layout')",
                $contents,
            );
            $this->assertStringNotContainsString(
                "@extends('website.layout')",
                $contents,
            );
        }
    }

    public function test_migrated_view_hashes_match_the_layout_only_transformation(): void
    {
        $expected = [
            'resources/views/website/page.blade.php' => 'ebce5b40607de71855f31ac4c701bde1fcd875c06f469473724afc6415a6aa45',
            'resources/views/website/legal-page.blade.php' => 'eb2474b5abce97483cc78d622cd75f0d3748c50281038a78c327abd012f2e394',
            'resources/views/website/faq.blade.php' => '8673fcba2ca42828dbd035c50f1dc6bb39a160a6869727a740908651dc122d90',
            'resources/views/website/testimonials.blade.php' => 'dc30cba1a3128918ef2c8e1272b11066674622f126a194cd0d9deb7f5ce8efe6',
        ];

        foreach ($expected as $relative => $hash) {
            $contents = file_get_contents(
                base_path($relative),
            );

            $this->assertIsString($contents);
            $this->assertSame(
                $hash,
                hash('sha256', $contents),
                'Unexpected content change: '.$relative,
            );
        }
    }

    public function test_routes_served_by_the_migrated_views_still_exist(): void
    {
        foreach (
            [
                'website.about',
                'website.services',
                'website.consultancy',
                'website.contact',
                'website.visas',
                'website.tickets',
                'website.hotels',
                'website.insurance',
                'website.faq',
                'website.testimonials',
                'website.legal.privacy',
                'website.legal.refund',
                'website.legal.cancellation',
                'website.legal.disclaimer',
                'website.legal.terms',
            ] as $routeName
        ) {
            $this->assertTrue(
                Route::has($routeName),
                'Missing migrated-page route: '.$routeName,
            );
        }
    }

    public function test_content_and_service_catalog_views_are_migrated(): void
    {
        foreach (
            [
                'blog-detail',
                'blog',
                'gallery-detail',
                'gallery',
                'promotion-detail',
                'promotions',
                'pakistan-tour-detail',
                'pakistan-tours',
                'umrah-package-detail',
                'umrah-packages',
            ] as $view
        ) {
            $contents = file_get_contents(
                resource_path(
                    'views/website/'.$view.'.blade.php',
                ),
            );

            $this->assertIsString($contents);
            $this->assertStringContainsString(
                "@extends('website.inner-v3-layout')",
                $contents,
            );
            $this->assertStringNotContainsString(
                "@extends('website.layout')",
                $contents,
            );
        }
    }
}
