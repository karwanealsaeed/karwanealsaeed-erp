<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsiteLegalPagesTest extends TestCase
{
    use RefreshDatabase;

    private const ROUTES = [
        'website.legal.privacy' => 'Privacy Policy',
        'website.legal.terms' => 'Terms & Conditions',
        'website.legal.refund' => 'Refund Policy',
        'website.legal.cancellation' => 'Cancellation Policy',
        'website.legal.disclaimer' => 'Website & Travel Service Disclaimer',
    ];

    public function test_all_legal_pages_render_with_canonical_metadata(): void
    {
        foreach (self::ROUTES as $routeName => $heading) {
            $this->get(route($routeName))
                ->assertOk()
                ->assertSee($heading)
                ->assertSee('name="robots" content="index, follow', false)
                ->assertSee(
                    'rel="canonical" href="'.route($routeName).'"',
                    false,
                );
        }
    }

    public function test_footer_contains_all_legal_links(): void
    {
        $response = $this->get(route('website.home'));

        foreach (array_keys(self::ROUTES) as $routeName) {
            $response->assertSee(route($routeName), false);
        }

        $response->assertSee('class="kes-footer-legal"', false);
    }

    public function test_sitemap_contains_all_legal_pages(): void
    {
        $response = $this->get(route('website.sitemap'));

        foreach (array_keys(self::ROUTES) as $routeName) {
            $response->assertSee(route($routeName), false);
        }
    }

    public function test_privacy_and_disclaimer_cover_karwan_ai_safely(): void
    {
        $this->get(route('website.legal.privacy'))
            ->assertOk()
            ->assertSee('Karwan AI', false)
            ->assertSee('Do not submit unnecessary passwords', false);

        $this->get(route('website.legal.disclaimer'))
            ->assertOk()
            ->assertSee('Karwan AI and automated responses', false)
            ->assertSee(
                'Confirm important matters with authorized staff.',
                false,
            );
    }

    public function test_refund_and_cancellation_pages_do_not_guarantee_refunds(): void
    {
        $this->get(route('website.legal.refund'))
            ->assertOk()
            ->assertSee(
                'We cannot approve an amount that the supplier does not authorize or return.',
                false,
            )
            ->assertSee('Any estimate is not a guarantee', false);

        $this->get(route('website.legal.cancellation'))
            ->assertOk()
            ->assertSee('do not automatically make tickets', false)
            ->assertSee('may be treated as a no-show', false);
    }

    public function test_urdu_legal_page_is_rtl_and_localized(): void
    {
        $this->get(
            route('website.legal.privacy', ['lang' => 'ur']),
        )
            ->assertOk()
            ->assertSee('<html lang="ur" dir="rtl">', false)
            ->assertSee('پرائیویسی پالیسی', false)
            ->assertSee('کاروان AI', false)
            ->assertSee('hreflang="en-PK"', false)
            ->assertSee('hreflang="ur-PK"', false);
    }

    public function test_legal_pages_show_dynamic_company_contact(): void
    {
        $this->get(route('website.legal.terms'))
            ->assertOk()
            ->assertSee('Karwan E Al Saeed Travel &amp; Tours', false)
            ->assertSee('+92 301 2592342', false)
            ->assertSee('karwanealsaeed@gmail.com', false);
    }
}
