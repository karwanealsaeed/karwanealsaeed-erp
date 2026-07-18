<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsitePwaTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_home_contains_pwa_metadata_and_service_worker(): void
    {
        $response = $this->get(route('website.home'));

        $response
            ->assertOk()
            ->assertSee('name="theme-color"', false)
            ->assertSee('content="#0B1F3A"', false)
            ->assertSee('rel="manifest"', false)
            ->assertSee('manifest.webmanifest', false)
            ->assertSee('rel="apple-touch-icon"', false)
            ->assertSee('favicon-32x32.png', false)
            ->assertSee("register('/service-worker.js'", false);
    }

    public function test_manifest_is_valid_and_contains_required_configuration(): void
    {
        $path = public_path('manifest.webmanifest');

        $this->assertFileExists($path);

        $manifest = json_decode(
            file_get_contents($path),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        $this->assertSame(
            'Karwan E Al Saeed Travel & Tours',
            $manifest['name'],
        );

        $this->assertSame('KES Travel', $manifest['short_name']);
        $this->assertSame('/', $manifest['start_url']);
        $this->assertSame('/', $manifest['scope']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertSame('#0B1F3A', $manifest['theme_color']);
        $this->assertSame('#FFFFFF', $manifest['background_color']);
        $this->assertCount(3, $manifest['icons']);

        foreach ($manifest['icons'] as $icon) {
            $this->assertArrayHasKey('src', $icon);
            $this->assertArrayHasKey('sizes', $icon);
            $this->assertArrayHasKey('type', $icon);

            $this->assertFileExists(
                public_path(ltrim($icon['src'], '/')),
            );
        }
    }

    public function test_pwa_icons_and_favicon_are_valid(): void
    {
        $expectedIcons = [
            'images/pwa/icon-192x192.png' => [192, 192],
            'images/pwa/icon-512x512.png' => [512, 512],
            'images/pwa/icon-maskable-512x512.png' => [512, 512],
            'images/pwa/apple-touch-icon.png' => [180, 180],
            'images/pwa/favicon-32x32.png' => [32, 32],
        ];

        foreach ($expectedIcons as $relativePath => $dimensions) {
            $path = public_path($relativePath);

            $this->assertFileExists($path);

            $imageSize = getimagesize($path);

            $this->assertNotFalse($imageSize);
            $this->assertSame($dimensions[0], $imageSize[0]);
            $this->assertSame($dimensions[1], $imageSize[1]);
        }

        $faviconPath = public_path('favicon.ico');

        $this->assertFileExists($faviconPath);
        $this->assertGreaterThan(0, filesize($faviconPath));
    }

    public function test_service_worker_has_safe_offline_behavior(): void
    {
        $path = public_path('service-worker.js');

        $this->assertFileExists($path);

        $serviceWorker = file_get_contents($path);

        $this->assertStringContainsString(
            "const OFFLINE_URL = '/offline.html';",
            $serviceWorker,
        );

        $this->assertStringContainsString(
            "request.method !== 'GET'",
            $serviceWorker,
        );

        $this->assertStringContainsString(
            "request.mode === 'navigate'",
            $serviceWorker,
        );

        $this->assertStringContainsString(
            "caches.match(OFFLINE_URL)",
            $serviceWorker,
        );

        foreach ([
            '/admin',
            '/customer',
            '/login',
            '/register',
            '/api',
            '/livewire',
            '/filament',
        ] as $excludedPath) {
            $this->assertStringContainsString(
                "'{$excludedPath}'",
                $serviceWorker,
            );
        }
    }

    public function test_offline_page_is_noindex_and_bilingual(): void
    {
        $path = public_path('offline.html');

        $this->assertFileExists($path);

        $offlinePage = file_get_contents($path);

        $this->assertStringContainsString(
            'name="robots" content="noindex, nofollow"',
            $offlinePage,
        );

        $this->assertStringContainsString(
            'You are offline',
            $offlinePage,
        );

        $this->assertStringContainsString(
            'آپ اس وقت آف لائن ہیں',
            $offlinePage,
        );

        $this->assertStringContainsString(
            'href="/"',
            $offlinePage,
        );
    }
}