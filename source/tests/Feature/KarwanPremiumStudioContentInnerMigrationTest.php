<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class KarwanPremiumStudioContentInnerMigrationTest extends TestCase
{
    public function test_controlled_content_views_use_v3_inner_layout(): void
    {
        foreach (
            [
                'blog-detail',
                'blog',
                'gallery-detail',
                'gallery',
                'promotion-detail',
                'promotions',
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

    public function test_migrated_content_hashes_match_layout_only_transformation(): void
    {
        $expected = [
            'resources/views/website/blog-detail.blade.php' => '905049af613448611d66e66d450a7406d39503018b3fe4cabe9fceb0f92f3973',
            'resources/views/website/blog.blade.php' => '3515e1f421b2bb6699914278d63f4e2972d98892362aeb2cd8d8418dd0454c2a',
            'resources/views/website/gallery-detail.blade.php' => 'b091f938715a8c71d2b290b8062e350b2914882350a9039e43aff32325e0f880',
            'resources/views/website/gallery.blade.php' => '10d7fe0f3b27a1e836367eb5deee2b4e3cfd1f7d3e6741e0463a651394723410',
            'resources/views/website/promotion-detail.blade.php' => '3b068bab28d8a7328f705eeb0503711def2363e08bc530df3077e1ac0191d8e8',
            'resources/views/website/promotions.blade.php' => '51d0e2777661b91961958b1de8144a5383473d9a3364203b47ba7253077ad32f',
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

    public function test_content_listing_and_detail_routes_still_exist(): void
    {
        foreach (
            [
                'website.blog',
                'website.blog.show',
                'website.gallery',
                'website.gallery.show',
                'website.promotions',
                'website.promotions.show',
            ] as $routeName
        ) {
            $this->assertTrue(
                Route::has($routeName),
                'Missing migrated content route: '.$routeName,
            );
        }
    }

    public function test_previously_migrated_core_views_remain_on_v3_layout(): void
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
        }
    }

    public function test_service_catalog_views_are_now_migrated(): void
    {
        foreach (
            [
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
