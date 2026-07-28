<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\GalleryItem;
use App\Models\UmrahPackage;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Routing\Route as LaravelRoute;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use Throwable;

class KarwanPremiumStudioPublicRouteSmokeTest extends TestCase
{
    use DatabaseTransactions;

    private const EXPECTED_WEBSITE_GET_ROUTE_COUNT = 28;

    private const DETAIL_LISTING_MAP = [
        'website.blog.show' => 'website.blog',
        'website.gallery.show' => 'website.gallery',
        'website.promotions.show' => 'website.promotions',
        'website.tours.show' => 'website.tours',
        'website.umrah.show' => 'website.umrah',
    ];

    public function test_real_application_database_is_available_transactionally(): void
    {
        $driver = strtolower(
            DB::connection()->getDriverName(),
        );

        $this->assertNotSame(
            'sqlite',
            $driver,
            'Public route smoke must not use SQLite memory.',
        );

        foreach (
            [
                'company_settings',
                'blog_posts',
            ] as $table
        ) {
            $this->assertTrue(
                Schema::hasTable($table),
                'Required real application table missing: '.$table,
            );
        }
    }

    public function test_public_website_get_route_inventory_is_complete(): void
    {
        $routes = $this->websiteGetRoutes();

        $this->assertCount(
            self::EXPECTED_WEBSITE_GET_ROUTE_COUNT,
            $routes,
        );

        foreach (
            [
                'website.home',
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
                'website.blog',
                'website.blog.show',
                'website.gallery',
                'website.gallery.show',
                'website.promotions',
                'website.promotions.show',
                'website.tours',
                'website.tours.show',
                'website.umrah',
                'website.umrah.show',
                'website.legal.privacy',
                'website.legal.refund',
                'website.legal.cancellation',
                'website.legal.disclaimer',
                'website.legal.terms',
                'website.language',
                'website.sitemap',
            ] as $routeName
        ) {
            $this->assertArrayHasKey(
                $routeName,
                $routes,
                'Missing public website route: '.$routeName,
            );
        }
    }

    public function test_all_static_public_routes_render_in_english_and_urdu(): void
    {
        $this->withoutExceptionHandling();
        foreach (['en', 'ur'] as $locale) {
            $this->selectLocale($locale);

            foreach ($this->websiteGetRoutes() as $name => $route) {
                if (
                    $name === 'website.language'
                    || $route->parameterNames() !== []
                ) {
                    continue;
                }

                $url = route($name, [], false);
                $response = $this->get($url);

                $this->assertNonErrorStatus(
                    $response->getStatusCode(),
                    $name.' ['.$locale.']',
                );

                if ($name === 'website.sitemap') {
                    $body = $response->getContent();

                    $this->assertTrue(
                        str_contains(
                            strtolower(
                                (string) $response->headers->get(
                                    'content-type',
                                    '',
                                ),
                            ),
                            'xml',
                        )
                        || str_contains($body, '<urlset')
                        || str_contains($body, '<sitemapindex'),
                        'Sitemap did not render XML.',
                    );

                    continue;
                }

                $this->assertV3HtmlResponse(
                    $response->getContent(),
                    $name,
                    $locale,
                );
            }
        }
    }

    public function test_language_switch_route_accepts_english_and_urdu(): void
    {
        $this->withoutExceptionHandling();
        foreach (['en', 'ur'] as $locale) {
            $response = $this->get(
                route('website.language', $locale, false),
            );

            $this->assertNonErrorStatus(
                $response->getStatusCode(),
                'website.language ['.$locale.']',
            );
        }
    }

    public function test_all_detail_routes_are_discoverable_and_render_in_both_locales(): void
    {
        $this->withoutExceptionHandling();

        foreach (['en', 'ur'] as $locale) {
            $this->selectLocale($locale);

            foreach (
                self::DETAIL_LISTING_MAP as $detailRouteName => $listingRouteName
            ) {
                $listingUrl = route(
                    $listingRouteName,
                    [],
                    false,
                );

                $listingResponse = $this->get($listingUrl);

                $this->assertNonErrorStatus(
                    $listingResponse->getStatusCode(),
                    $listingRouteName.' ['.$locale.']',
                );

                $detailUrl = $this->discoverRouteUrl(
                    $listingResponse->getContent(),
                    $listingUrl,
                    $detailRouteName,
                );

                if (
                    $detailRouteName === 'website.gallery.show'
                    && $detailUrl === null
                ) {
                    $this->assertFalse(
                        GalleryItem::query()
                            ->published()
                            ->exists(),
                        'Gallery listing has no detail link even though a Published gallery item exists.',
                    );

                    $this->assertV3HtmlResponse(
                        $listingResponse->getContent(),
                        $listingRouteName,
                        $locale,
                    );

                    continue;
                }

                if (
                    $detailRouteName === 'website.umrah.show'
                    && $detailUrl === null
                ) {
                    $this->assertFalse(
                        UmrahPackage::query()
                            ->publiclyAvailable()
                            ->exists(),
                        'Umrah listing has no detail link even though a current/future Available package exists.',
                    );

                    $this->assertV3HtmlResponse(
                        $listingResponse->getContent(),
                        $listingRouteName,
                        $locale,
                    );

                    continue;
                }

                $this->assertNotNull(
                    $detailUrl,
                    'No real detail link discovered for '
                    .$detailRouteName
                    .' from '
                    .$listingRouteName
                    .' ['.$locale.'].',
                );

                $detailResponse = $this->get($detailUrl);

                $this->assertNonErrorStatus(
                    $detailResponse->getStatusCode(),
                    $detailRouteName.' ['.$locale.'] '.$detailUrl,
                );

                $this->assertV3HtmlResponse(
                    $detailResponse->getContent(),
                    $detailRouteName,
                    $locale,
                );
            }
        }
    }

    public function test_gallery_listing_matches_published_scope(): void
    {
        foreach (['en', 'ur'] as $locale) {
            $this->selectLocale($locale);

            $listingUrl = route(
                'website.gallery',
                [],
                false,
            );

            $response = $this->get($listingUrl);

            $this->assertNonErrorStatus(
                $response->getStatusCode(),
                'website.gallery ['.$locale.']',
            );

            $detailUrl = $this->discoverRouteUrl(
                $response->getContent(),
                $listingUrl,
                'website.gallery.show',
            );

            $publishedItem = GalleryItem::query()
                ->published()
                ->orderBy('id')
                ->first();

            if ($publishedItem === null) {
                $this->assertNull(
                    $detailUrl,
                    'Gallery listing rendered a detail link without a Published gallery item.',
                );

                $this->assertV3HtmlResponse(
                    $response->getContent(),
                    'website.gallery',
                    $locale,
                );

                continue;
            }

            $this->assertNotNull(
                $detailUrl,
                'Published gallery item is not linked from the public listing.',
            );

            $detailResponse = $this->get($detailUrl);

            $this->assertNonErrorStatus(
                $detailResponse->getStatusCode(),
                'website.gallery.show ['.$locale.']',
            );

            $this->assertV3HtmlResponse(
                $detailResponse->getContent(),
                'website.gallery.show',
                $locale,
            );
        }
    }

    public function test_umrah_listing_matches_public_availability_scope(): void
    {
        foreach (['en', 'ur'] as $locale) {
            $this->selectLocale($locale);

            $listingUrl = route(
                'website.umrah',
                [],
                false,
            );

            $response = $this->get($listingUrl);

            $this->assertNonErrorStatus(
                $response->getStatusCode(),
                'website.umrah ['.$locale.']',
            );

            $detailUrl = $this->discoverRouteUrl(
                $response->getContent(),
                $listingUrl,
                'website.umrah.show',
            );

            $publicPackage = UmrahPackage::query()
                ->publiclyAvailable()
                ->orderBy('id')
                ->first();

            if ($publicPackage === null) {
                $this->assertNull(
                    $detailUrl,
                    'Umrah listing rendered a detail link without a current/future Available package.',
                );

                $this->assertV3HtmlResponse(
                    $response->getContent(),
                    'website.umrah',
                    $locale,
                );

                continue;
            }

            $this->assertNotNull(
                $detailUrl,
                'Current/future Available Umrah package is not linked from the public listing.',
            );

            $detailResponse = $this->get($detailUrl);

            $this->assertNonErrorStatus(
                $detailResponse->getStatusCode(),
                'website.umrah.show ['.$locale.']',
            );

            $this->assertV3HtmlResponse(
                $detailResponse->getContent(),
                'website.umrah.show',
                $locale,
            );
        }
    }

    /**
     * @return array<string, LaravelRoute>
     */
    private function websiteGetRoutes(): array
    {
        $routes = [];

        foreach (Route::getRoutes() as $route) {
            $name = $route->getName();

            if (
                ! is_string($name)
                || ! str_starts_with($name, 'website.')
                || ! array_intersect(
                    ['GET', 'HEAD'],
                    $route->methods(),
                )
            ) {
                continue;
            }

            $routes[$name] = $route;
        }

        ksort($routes);

        return $routes;
    }

    private function selectLocale(string $locale): void
    {
        $this->withSession(
            [
                'locale' => $locale,
                'language' => $locale,
                'app_locale' => $locale,
            ],
        );

        app()->setLocale($locale);

        $switchResponse = $this->get(
            route('website.language', $locale, false),
        );

        $this->assertNonErrorStatus(
            $switchResponse->getStatusCode(),
            'website.language ['.$locale.']',
        );

        $this->withSession(
            [
                'locale' => $locale,
                'language' => $locale,
                'app_locale' => $locale,
            ],
        );

        app()->setLocale($locale);
    }

    private function assertNonErrorStatus(
        int $status,
        string $context,
    ): void {
        $this->assertGreaterThanOrEqual(
            200,
            $status,
            'Unexpected status for '.$context,
        );

        $this->assertLessThan(
            400,
            $status,
            'Server/client error status for '.$context,
        );
    }

    private function assertV3HtmlResponse(
        string $html,
        string $routeName,
        string $locale,
    ): void {
        $this->assertStringContainsString(
            'karwan-premium-studio-v3.css',
            $html,
            'V3 CSS missing: '.$routeName.' ['.$locale.']',
        );

        $this->assertStringNotContainsString(
            'Customer Portal',
            $html,
            'Old portal label rendered: '
            .$routeName
            .' ['
            .$locale
            .']',
        );

        $expectedDirection = $locale === 'ur'
            ? 'dir="rtl"'
            : 'dir="ltr"';

        $this->assertStringContainsString(
            $expectedDirection,
            $html,
            'HTML direction mismatch: '
            .$routeName
            .' ['
            .$locale
            .']',
        );

        if ($routeName === 'website.home') {
            $this->assertStringContainsString(
                'page-home',
                $html,
            );

            return;
        }

        $this->assertStringContainsString(
            'data-kes-v3-page="inner"',
            $html,
            'Inner V3 shell missing: '
            .$routeName
            .' ['
            .$locale
            .']',
        );
    }

    private function discoverRouteUrl(
        string $html,
        string $listingUrl,
        string $expectedRouteName,
    ): ?string {
        preg_match_all(
            '/\bhref\s*=\s*(["\'])(.*?)\1/is',
            $html,
            $matches,
        );

        foreach ($matches[2] ?? [] as $href) {
            $candidate = $this->normalizeLocalUrl(
                html_entity_decode(
                    trim((string) $href),
                    ENT_QUOTES | ENT_HTML5,
                    'UTF-8',
                ),
                $listingUrl,
            );

            if ($candidate === null) {
                continue;
            }

            try {
                $request = Request::create(
                    $candidate,
                    'GET',
                );

                $matchedRoute = app('router')
                    ->getRoutes()
                    ->match($request);
            } catch (Throwable) {
                continue;
            }

            if ($matchedRoute->getName() === $expectedRouteName) {
                return $candidate;
            }
        }

        return null;
    }

    private function normalizeLocalUrl(
        string $href,
        string $listingUrl,
    ): ?string {
        if (
            $href === ''
            || str_starts_with($href, '#')
            || preg_match(
                '/^(?:mailto|tel|javascript|data):/i',
                $href,
            ) === 1
        ) {
            return null;
        }

        $parts = parse_url($href);

        if ($parts === false) {
            return null;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));

        if (
            $scheme !== ''
            && ! in_array($scheme, ['http', 'https'], true)
        ) {
            return null;
        }

        $path = (string) ($parts['path'] ?? '');

        if ($path === '') {
            return null;
        }

        if (! str_starts_with($path, '/')) {
            $listingPath = (string) parse_url(
                $listingUrl,
                PHP_URL_PATH,
            );

            $baseDirectory = rtrim(
                str_replace(
                    '\\',
                    '/',
                    dirname($listingPath),
                ),
                '/',
            );

            $path =
                ($baseDirectory === '' ? '' : $baseDirectory)
                .'/'
                .$path;
        }

        $query = (string) ($parts['query'] ?? '');

        return $path.($query === '' ? '' : '?'.$query);
    }
}
