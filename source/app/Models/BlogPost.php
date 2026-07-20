<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    protected $fillable = [
        'title',
        'title_ur',
        'slug',
        'excerpt',
        'excerpt_ur',
        'content',
        'content_ur',
        'category',
        'category_ur',
        'featured_image_path',
        'image_alt_text',
        'image_alt_text_ur',
        'author_name',
        'tags',
        'seo_title',
        'seo_title_ur',
        'seo_description',
        'seo_description_ur',
        'status',
        'is_featured',
        'sort_order',
        'view_count',
        'published_at',
    ];

    protected $casts = [
        'tags' => 'array',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
        'view_count' => 'integer',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (BlogPost $blogPost): void {
            if (blank($blogPost->slug)) {
                $blogPost->slug = static::generateUniqueSlug(
                    $blogPost->title,
                    $blogPost->getKey(),
                );
            } else {
                $blogPost->slug = static::generateUniqueSlug(
                    $blogPost->slug,
                    $blogPost->getKey(),
                );
            }

            if (
                $blogPost->status === 'Published'
                && blank($blogPost->published_at)
            ) {
                $blogPost->published_at = now();
            }
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'Published')
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getLocalizedTitleAttribute(): string
    {
        return app()->isLocale('ur')
            && filled($this->title_ur)
                ? $this->title_ur
                : $this->title;
    }

    public function getLocalizedExcerptAttribute(): ?string
    {
        return app()->isLocale('ur')
            && filled($this->excerpt_ur)
                ? $this->excerpt_ur
                : $this->excerpt;
    }

    public function getLocalizedContentAttribute(): string
    {
        return app()->isLocale('ur')
            && filled($this->content_ur)
                ? $this->content_ur
                : $this->content;
    }

    public function getLocalizedCategoryAttribute(): ?string
    {
        return app()->isLocale('ur')
            && filled($this->category_ur)
                ? $this->category_ur
                : $this->category;
    }

    public function getLocalizedImageAltTextAttribute(): string
    {
        if (
            app()->isLocale('ur')
            && filled($this->image_alt_text_ur)
        ) {
            return $this->image_alt_text_ur;
        }

        return $this->image_alt_text
            ?: $this->localized_title;
    }

    public function getLocalizedSeoTitleAttribute(): string
    {
        if (
            app()->isLocale('ur')
            && filled($this->seo_title_ur)
        ) {
            return $this->seo_title_ur;
        }

        return $this->seo_title
            ?: $this->localized_title;
    }

    public function getLocalizedSeoDescriptionAttribute(): ?string
    {
        if (
            app()->isLocale('ur')
            && filled($this->seo_description_ur)
        ) {
            return $this->seo_description_ur;
        }

        return $this->seo_description
            ?: $this->localized_excerpt;
    }

    public function getReadingTimeAttribute(): int
    {
        $content = trim(
            strip_tags($this->localized_content),
        );

        if ($content === '') {
            return 1;
        }

        $words = preg_split(
            '/\s+/u',
            $content,
            -1,
            PREG_SPLIT_NO_EMPTY,
        );

        return max(
            1,
            (int) ceil(count($words) / 200),
        );
    }

    private static function generateUniqueSlug(
        string $value,
        int|string|null $ignoreId = null,
    ): string {
        $baseSlug = Str::slug($value);

        if ($baseSlug === '') {
            $baseSlug = 'blog-post-'.Str::lower(
                Str::random(8),
            );
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            static::query()
                ->when(
                    filled($ignoreId),
                    fn (Builder $query) => $query->whereKeyNot(
                        $ignoreId,
                    ),
                )
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}