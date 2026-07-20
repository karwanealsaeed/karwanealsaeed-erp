<?php

namespace Tests\Feature;

use App\Models\Faq;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsiteFaqTest extends TestCase
{
    use RefreshDatabase;

    public function test_faq_listing_page_opens(): void
    {
        $response = $this->get(
            route('website.faq'),
        );

        $response
            ->assertOk()
            ->assertSee('Frequently Asked Questions');
    }

    public function test_listing_only_displays_current_published_faqs(): void
    {
        $published = $this->createFaq([
            'question' => 'Published FAQ Question',
            'status' => 'Published',
        ]);

        $draft = $this->createFaq([
            'question' => 'Draft FAQ Question',
            'status' => 'Draft',
        ]);

        $future = $this->createFaq([
            'question' => 'Future FAQ Question',
            'status' => 'Published',
            'published_at' => now()->addDay(),
        ]);

        $response = $this->get(
            route('website.faq'),
        );

        $response
            ->assertOk()
            ->assertSee($published->question)
            ->assertDontSee($draft->question)
            ->assertDontSee($future->question);
    }

    public function test_listing_filters_faqs_by_category(): void
    {
        $umrahFaq = $this->createFaq([
            'question' => 'Umrah Documents Question',
            'category' => 'Umrah',
        ]);

        $visaFaq = $this->createFaq([
            'question' => 'Visit Visa Question',
            'category' => 'Visit Visa',
        ]);

        $response = $this->get(
            route('website.faq', [
                'category' => 'Umrah',
            ]),
        );

        $response
            ->assertOk()
            ->assertSee($umrahFaq->question)
            ->assertDontSee($visaFaq->question);
    }

    public function test_urdu_listing_displays_localized_faq_content(): void
    {
        $faq = $this->createFaq([
            'question' => 'English FAQ Question',
            'question_ur' => 'عمرہ ویزا کیسے حاصل کریں؟',
            'answer' => 'English FAQ answer.',
            'answer_ur' => 'ہماری ٹیم مکمل رہنمائی فراہم کرتی ہے۔',
        ]);

        $response = $this->get(
            route('website.faq', [
                'lang' => 'ur',
            ]),
        );

        $response
            ->assertOk()
            ->assertSee($faq->question_ur)
            ->assertSee($faq->answer_ur)
            ->assertDontSee($faq->answer);
    }

    public function test_featured_faqs_are_displayed_first(): void
    {
        $regular = $this->createFaq([
            'question' => 'Regular FAQ Question',
            'is_featured' => false,
            'sort_order' => 1,
        ]);

        $featured = $this->createFaq([
            'question' => 'Featured FAQ Question',
            'is_featured' => true,
            'sort_order' => 100,
        ]);

        $response = $this->get(
            route('website.faq'),
        );

        $response
            ->assertOk()
            ->assertSeeInOrder([
                $featured->question,
                $regular->question,
            ]);
    }

    public function test_filtered_faq_page_is_noindex(): void
    {
        $this->createFaq([
            'category' => 'Umrah',
        ]);

        $response = $this->get(
            route('website.faq', [
                'category' => 'Umrah',
            ]),
        );

        $response
            ->assertOk()
            ->assertSee(
                'noindex, follow, max-image-preview:large',
            );
    }

    public function test_faq_page_contains_faq_schema(): void
    {
        $faq = $this->createFaq([
            'question' => 'Schema FAQ Question',
            'answer' => 'Schema FAQ answer.',
        ]);

        $response = $this->get(
            route('website.faq'),
        );

        $response
            ->assertOk()
            ->assertSee('FAQPage')
            ->assertSee($faq->question)
            ->assertSee($faq->answer);
    }

    private function createFaq(
        array $attributes = [],
    ): Faq {
        return Faq::create(array_merge([
            'question' => 'Test FAQ Question',
            'question_ur' => 'ٹیسٹ سوال',
            'answer' =>
                'This is a helpful test FAQ answer.',
            'answer_ur' =>
                'یہ ایک مفید ٹیسٹ جواب ہے۔',
            'category' => 'Umrah',
            'category_ur' => 'عمرہ',
            'status' => 'Published',
            'is_featured' => false,
            'sort_order' => 1,
            'published_at' => now()->subMinute(),
        ], $attributes));
    }
}