<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GalleryItem extends Model
{
    protected $fillable = [
        'title',
        'title_ur',
        'slug',
        'category',
        'category_ur',
        'album',
        'album_ur',
        'media_type',
        'image_path',
        'thumbnail_path',
        'video_url',
        'alt_text',
        'alt_text_ur',
        'caption',
        'caption_ur',
        'location',
        'location_ur',
        'event_date',
        'photographer',
        'status',
        'is_featured',
        'sort_order',
        'published_at',
    ];

    protected $casts = [
        'event_date' => 'date',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (GalleryItem $item): void {
            if (blank($item->slug)) {
                $baseSlug = Str::slug($item->title);

                if (blank($baseSlug)) {
                    $baseSlug = 'gallery-item';
                }

                $slug = $baseSlug;
                $counter = 2;

                while (
                    self::query()
                        ->where('slug', $slug)
                        ->when(
                            $item->exists,
                            fn (Builder $query): Builder => $query->where(
                                $item->getKeyName(),
                                '!=',
                                $item->getKey(),
                            ),
                        )
                        ->exists()
                ) {
                    $slug = $baseSlug.'-'.$counter;
                    $counter++;
                }

                $item->slug = $slug;
            }

            if (
                $item->status === 'Published'
                && blank($item->published_at)
            ) {
                $item->published_at = now();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
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

    public function getLocalizedTitleAttribute(): string
    {
        return app()->isLocale('ur') && filled($this->title_ur)
            ? $this->title_ur
            : $this->title;
    }

    public function getLocalizedCategoryAttribute(): ?string
    {
        return app()->isLocale('ur') && filled($this->category_ur)
            ? $this->category_ur
            : $this->category;
    }

    public function getLocalizedAlbumAttribute(): ?string
    {
        return app()->isLocale('ur') && filled($this->album_ur)
            ? $this->album_ur
            : $this->album;
    }

    public function getLocalizedAltTextAttribute(): string
    {
        if (
            app()->isLocale('ur')
            && filled($this->alt_text_ur)
        ) {
            return $this->alt_text_ur;
        }

        return $this->alt_text ?: $this->localized_title;
    }

    public function getLocalizedCaptionAttribute(): ?string
{
    return app()->isLocale('ur') && filled($this->caption_ur)
        ? $this->caption_ur
        : $this->caption;
}

public function getLocalizedLocationAttribute(): ?string
{
    return app()->isLocale('ur') && filled($this->location_ur)
        ? $this->location_ur
        : $this->location;
}

public function getDisplayImageAttribute(): ?string
{
    return $this->thumbnail_path ?: $this->image_path;
}

public function getIsVideoAttribute(): bool
{
    return $this->media_type === 'Video';
}
}