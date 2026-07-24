<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicFooterContentLinksTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_footer_contains_all_public_content_index_links(): void
    {
        $response = $this->get(route('website.home'));

        $response->assertOk();

        foreach (
            [
                'website.gallery',
                'website.promotions',
                'website.faq',
                'website.blog',
                'website.testimonials',
                'website.tours',
            ] as $routeName
        ) {
            $response->assertSee(
                'href="'.route($routeName).'"',
                false,
            );
        }
    }

    public function test_new_footer_content_links_open_successfully(): void
    {
        foreach (
            [
                'website.faq',
                'website.blog',
                'website.testimonials',
            ] as $routeName
        ) {
            $this->get(route($routeName))
                ->assertOk();
        }
    }
}
