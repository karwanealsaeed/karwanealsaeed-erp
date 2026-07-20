<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Promotion extends Model
{
    protected $fillable = [
        'title',
        'title_ur',
        'slug',
        'subtitle',
        'subtitle_ur',
        'description',
        'description_ur',
        'promotion_type',
        'promo_code',
        'discount_type',
        'discount_value',
        'currency',
        'original_price',
        'offer_price',
        'featured_image_path',
        'badge_text',
        'badge_text_ur',
        'cta_text',
        'cta_text_ur',
        'target_url',
        'terms',
        'terms_ur',
        'starts_at',
        'ends_at',
        'status',
        'is_featured',
        'sort_order',
        'published_at',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'original_price' => 'decimal:2',
        'offer_price' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'status' => 'string',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (Promotion $promotion): void {
            $promotion->slug = static::generateUniqueSlug(
                filled($promotion->slug)
                    ? $promotion->slug
                    : $promotion->title,
                $promotion->getKey(),
            );

            if (
                $promotion->status === 'Published'
                && blank($promotion->published_at)
            ) {
                $promotion->published_at = now();
            }

            if (filled($promotion->currency)) {
                $promotion->currency = Str::upper(
                    $promotion->currency,
                );
            }

            if (filled($promotion->promo_code)) {
                $promotion->promo_code = Str::upper(
                    trim($promotion->promo_code),
                );
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

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->published()
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
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

    public function getLocalizedSubtitleAttribute(): ?string
    {
        return app()->isLocale('ur')
            && filled($this->subtitle_ur)
                ? $this->subtitle_ur
                : $this->subtitle;
    }

    public function getLocalizedDescriptionAttribute(): ?string
    {
        return app()->isLocale('ur')
            && filled($this->description_ur)
                ? $this->description_ur
                : $this->description;
    }

    public function getLocalizedBadgeTextAttribute(): ?string
    {
        return app()->isLocale('ur')
            && filled($this->badge_text_ur)
                ? $this->badge_text_ur
                : $this->badge_text;
    }

    public function getLocalizedCtaTextAttribute(): string
    {
        if (
            app()->isLocale('ur')
            && filled($this->cta_text_ur)
        ) {
            return $this->cta_text_ur;
        }

        return $this->cta_text
            ?: (
                app()->isLocale('ur')
                    ? 'آفر حاصل کریں'
                    : 'Get This Offer'
            );
    }

    public function getLocalizedTermsAttribute(): ?string
    {
        return app()->isLocale('ur')
            && filled($this->terms_ur)
                ? $this->terms_ur
                : $this->terms;
    }

    public function getIsActiveAttribute(): bool
    {
        if ($this->status !== 'Published') {
            return false;
        }

        if (
            filled($this->published_at)
            && $this->published_at->isFuture()
        ) {
            return false;
        }

        if (
            filled($this->starts_at)
            && $this->starts_at->isFuture()
        ) {
            return false;
        }

        if (
            filled($this->ends_at)
            && $this->ends_at->isPast()
        ) {
            return false;
        }

        return true;
    }

    public function getDiscountLabelAttribute(): ?string
    {
        if (blank($this->discount_value)) {
            return null;
        }

        if ($this->discount_type === 'Percentage') {
            return rtrim(
                rtrim(
                    number_format(
                        (float) $this->discount_value,
                        2,
                        '.',
                        '',
                    ),
                    '0',
                ),
                '.',
            ).'% OFF';
        }

        $currency = $this->currency ?: 'PKR';

        return $currency.' '.number_format(
            (float) $this->discount_value,
            0,
        ).' OFF';
    }

    private static function generateUniqueSlug(
        string $value,
        int|string|null $ignoreId = null,
    ): string {
        $baseSlug = Str::slug($value);

        if ($baseSlug === '') {
            $baseSlug = 'promotion-'.Str::lower(
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