<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsiteLocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_english_is_the_default_public_website_language(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('lang="en"', false)
            ->assertSee('dir="ltr"', false)
            ->assertSee('Your Trusted Partner for', false);
    }

    public function test_language_route_switches_the_website_to_urdu(): void
    {
        $this->from('/')
            ->get('/language/ur')
            ->assertRedirect('/')
            ->assertSessionHas('locale', 'ur');

        $this->get('/')
            ->assertOk()
            ->assertSee('lang="ur"', false)
            ->assertSee('dir="rtl"', false)
            ->assertSee('پریمیم سفری اور عمرہ خدمات', false)
            ->assertSee('کاروان اے آئی اسسٹنٹ', false);
    }

    public function test_language_route_can_switch_back_to_english(): void
    {
        $this->withSession(['locale' => 'ur']);

        $this->from('/')
            ->get('/language/en')
            ->assertRedirect('/')
            ->assertSessionHas('locale', 'en');

        $this->get('/')
            ->assertOk()
            ->assertSee('lang="en"', false)
            ->assertSee('dir="ltr"', false);
    }

    public function test_invalid_language_returns_not_found(): void
    {
        $this->get('/language/fr')
            ->assertNotFound();
    }

    public function test_public_service_page_uses_urdu_content(): void
    {
        $this->withSession(['locale' => 'ur'])
            ->get('/our-services')
            ->assertOk()
            ->assertSee('lang="ur"', false)
            ->assertSee('dir="rtl"', false)
            ->assertSee('ہماری سفری خدمات', false)
            ->assertSee('عمرہ سفر کے لیے مکمل معاونت', false);
    }

    public function test_umrah_listing_uses_urdu_content(): void
    {
        $this->withSession(['locale' => 'ur'])
            ->get('/umrah-packages')
            ->assertOk()
            ->assertSee('دستیاب عمرہ پیکجز تلاش کریں', false)
            ->assertSee('فلٹرز لاگو کریں', false);
    }
}