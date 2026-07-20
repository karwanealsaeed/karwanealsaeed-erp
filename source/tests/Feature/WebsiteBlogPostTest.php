<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsiteBlogPostTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_listing_page_opens(): void
    {
        $response = $this->get(
            route('website.blog'),
        );

        $response
            ->assertOk()
            ->assertSee('Blog & Latest News');
    }

    public function test_listing_only_displays_current_published_posts(): void
    {
        $published = $this->createBlogPost([
            'title' => 'Published Travel Article',
        ]);

        $draft = $this->createBlogPost([
            'title' => 'Draft Travel Article',
            'status' => 'Draft',
        ]);

        $future = $this->createBlogPost([
            'title' => 'Future Travel Article',
            'published_at' => now()->addDay(),
        ]);

        $response = $this->get(
            route('website.blog'),
        );

        $response
            ->assertOk()
            ->assertSee($published->title)
            ->assertDontSee($draft->title)
            ->assertDontSee($future->title);
    }

    public function test_listing_filters_posts_by_category(): void
    {
        $umrahPost = $this->createBlogPost([
            'title' => 'Umrah Preparation Guide',
            'category' => 'Umrah Guide',
        ]);

        $visaPost = $this->createBlogPost([
            'title' => 'Visit Visa News',
            'category' => 'Visa News',
        ]);

        $response = $this->get(
            route('website.blog', [
                'category' => 'Umrah Guide',
            ]),
        );

        $response
            ->assertOk()
            ->assertSee($umrahPost->title)
            ->assertDontSee($visaPost->title);
    }

    public function test_listing_searches_published_posts(): void
    {
        $matched = $this->createBlogPost([
            'title' => 'Complete Umrah Documents Guide',
        ]);

        $unmatched = $this->createBlogPost([
            'title' => 'Pakistan Northern Areas Tour',
        ]);

        $response = $this->get(
            route('website.blog', [
                'search' => 'Umrah Documents',
            ]),
        );

        $response
            ->assertOk()
            ->assertSee($matched->title)
            ->assertDontSee($unmatched->title);
    }

    public function test_published_blog_detail_opens_and_increments_views(): void
    {
        $blogPost = $this->createBlogPost([
            'title' => 'Published Blog Detail',
            'view_count' => 4,
        ]);

        $response = $this->get(
            route('website.blog.show', [
                'blogPost' => $blogPost,
            ]),
        );

        $response
            ->assertOk()
            ->assertSee($blogPost->title)
            ->assertSee($blogPost->content);

        $this->assertSame(
            5,
            $blogPost->refresh()->view_count,
        );
    }

    public function test_draft_blog_detail_returns_not_found(): void
    {
        $blogPost = $this->createBlogPost([
            'title' => 'Private Draft Blog',
            'status' => 'Draft',
        ]);

        $response = $this->get(
            route('website.blog.show', [
                'blogPost' => $blogPost,
            ]),
        );

        $response->assertNotFound();
    }

    public function test_future_blog_detail_returns_not_found(): void
    {
        $blogPost = $this->createBlogPost([
            'title' => 'Scheduled Future Blog',
            'published_at' => now()->addDay(),
        ]);

        $response = $this->get(
            route('website.blog.show', [
                'blogPost' => $blogPost,
            ]),
        );

        $response->assertNotFound();
    }

    public function test_urdu_blog_displays_localized_content(): void
    {
        $blogPost = $this->createBlogPost([
            'title' => 'English Travel Guide',
            'title_ur' => 'مکمل عمرہ سفری رہنمائی',
            'excerpt' => 'English article excerpt.',
            'excerpt_ur' => 'عمرہ سفر کے متعلق مفید رہنمائی۔',
            'content' => 'English article content.',
            'content_ur' => 'عمرہ سفر کی مکمل تیاری اور ضروری ہدایات۔',
        ]);

        $response = $this->get(
            route('website.blog.show', [
                'blogPost' => $blogPost,
                'lang' => 'ur',
            ]),
        );

        $response
            ->assertOk()
            ->assertSee($blogPost->title_ur)
            ->assertSee($blogPost->content_ur)
            ->assertDontSee($blogPost->content);
    }

    public function test_filtered_blog_listing_is_noindex(): void
    {
        $this->createBlogPost();

        $response = $this->get(
            route('website.blog', [
                'category' => 'Umrah Guide',
            ]),
        );

        $response
            ->assertOk()
            ->assertSee(
                'noindex, follow, max-image-preview:large',
            );
    }

    public function test_blog_detail_contains_blog_posting_schema(): void
    {
        $blogPost = $this->createBlogPost([
            'title' => 'Structured Data Blog Article',
        ]);

        $response = $this->get(
            route('website.blog.show', [
                'blogPost' => $blogPost,
            ]),
        );

        $response
            ->assertOk()
            ->assertSee('BlogPosting')
            ->assertSee($blogPost->title);
    }

    public function test_sitemap_includes_only_current_published_blog_posts(): void
    {
        $published = $this->createBlogPost([
            'title' => 'Published Sitemap Blog',
        ]);

        $draft = $this->createBlogPost([
            'title' => 'Draft Sitemap Blog',
            'status' => 'Draft',
        ]);

        $future = $this->createBlogPost([
            'title' => 'Future Sitemap Blog',
            'published_at' => now()->addDay(),
        ]);

        $response = $this->get('/sitemap.xml');

        $response
            ->assertOk()
            ->assertHeader(
                'Content-Type',
                'application/xml; charset=UTF-8',
            )
            ->assertSee(
                route('website.blog.show', [
                    'blogPost' => $published,
                ]),
                false,
            )
            ->assertDontSee(
                route('website.blog.show', [
                    'blogPost' => $draft,
                ]),
                false,
            )
            ->assertDontSee(
                route('website.blog.show', [
                    'blogPost' => $future,
                ]),
                false,
            );
    }

    private function createBlogPost(
        array $attributes = [],
    ): BlogPost {
        return BlogPost::create(array_merge([
            'title' => 'Test Travel Blog Article',
            'title_ur' => 'ٹیسٹ سفری مضمون',
            'excerpt' => 'A helpful travel article excerpt.',
            'excerpt_ur' => 'ایک مفید سفری مضمون کا خلاصہ۔',
            'content' =>
                'This is complete helpful travel article content.',
            'content_ur' =>
                'یہ ایک مکمل اور مفید سفری مضمون ہے۔',
            'category' => 'Umrah Guide',
            'category_ur' => 'عمرہ رہنمائی',
            'author_name' => 'Karwan E Al Saeed Team',
            'tags' => [
                'Umrah',
                'Travel',
            ],
            'status' => 'Published',
            'is_featured' => false,
            'sort_order' => 1,
            'view_count' => 0,
            'published_at' => now()->subMinute(),
        ], $attributes));
    }
}