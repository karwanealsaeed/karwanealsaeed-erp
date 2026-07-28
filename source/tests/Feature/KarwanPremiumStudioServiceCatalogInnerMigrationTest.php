<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class KarwanPremiumStudioServiceCatalogInnerMigrationTest extends TestCase
{
    public function test_service_catalog_views_use_v3_inner_layout(): void
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

    public function test_service_catalog_hashes_match_layout_only_transformation(): void
    {
        $expected = [
            'resources/views/website/pakistan-tour-detail.blade.php' => 'faae817a4384e9db12db0d3c8a00fabd947f99f9617a354effbe9bbf76f93342',
            'resources/views/website/pakistan-tours.blade.php' => 'bfbc8491ecfc9defcd6d962b078c1deb0b33be515a4cdb4578cda4de9dcfe39e',
            'resources/views/website/umrah-package-detail.blade.php' => '3e324b26ef8ca703350db35805090a43b61712fb2f9100084cbe339ac5d05c22',
            'resources/views/website/umrah-packages.blade.php' => 'eb5cb93ed26f1b60c3045713428ad8e1655d0666f44bf08cd78b4adc67937e1e',
        ];

        foreach ($expected as $relative => $hash) {
            $contents = file_get_contents(
                base_path($relative),
            );

            $this->assertIsString($contents);
            $this->assertSame(
                $hash,
                hash('sha256', $contents),
                'Unexpected service-catalog content change: '.$relative,
            );
        }
    }

    public function test_service_catalog_listing_and_detail_routes_still_exist(): void
    {
        foreach (
            [
                'website.tours',
                'website.tours.show',
                'website.umrah',
                'website.umrah.show',
            ] as $routeName
        ) {
            $this->assertTrue(
                Route::has($routeName),
                'Missing service-catalog route: '.$routeName,
            );
        }
    }

    public function test_all_fourteen_public_inner_views_now_use_v3_layout(): void
    {
        foreach (
            [
                'page',
                'legal-page',
                'faq',
                'testimonials',
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
