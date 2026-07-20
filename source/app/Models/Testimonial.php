<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = [
        'customer_name',
        'customer_name_ur',
        'designation',
        'designation_ur',
        'company_name',
        'location',
        'location_ur',
        'country',
        'photo_path',
        'rating',
        'testimonial',
        'testimonial_ur',
        'service_type',
        'travel_date',
        'video_url',
        'status',
        'is_featured',
        'sort_order',
        'published_at',
    ];

    protected $casts = [
        'rating' => 'integer',
        'travel_date' => 'date',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (Testimonial $testimonial): void {
            if (
                $testimonial->status === 'Published'
                && blank($testimonial->published_at)
            ) {
                $testimonial->published_at = now();
            }

            if ($testimonial->rating < 1) {
                $testimonial->rating = 1;
            }

            if ($testimonial->rating > 5) {
                $testimonial->rating = 5;
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

    public function getLocalizedCustomerNameAttribute(): string
    {
        return app()->isLocale('ur')
            && filled($this->customer_name_ur)
                ? $this->customer_name_ur
                : $this->customer_name;
    }

    public function getLocalizedDesignationAttribute(): ?string
    {
        return app()->isLocale('ur')
            && filled($this->designation_ur)
                ? $this->designation_ur
                : $this->designation;
    }

    public function getLocalizedLocationAttribute(): ?string
    {
        return app()->isLocale('ur')
            && filled($this->location_ur)
                ? $this->location_ur
                : $this->location;
    }

    public function getLocalizedTestimonialAttribute(): string
    {
        return app()->isLocale('ur')
            && filled($this->testimonial_ur)
                ? $this->testimonial_ur
                : $this->testimonial;
    }

    public function getIsVideoAttribute(): bool
    {
        return filled($this->video_url);
    }
}