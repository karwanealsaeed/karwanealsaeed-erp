<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PakistanTour extends Model
{
    protected $fillable = [
        'tour_no',
        'title',
        'title_ur',
        'slug',
        'subtitle',
        'subtitle_ur',
        'destination',
        'province',
        'departure_city',
        'duration_days',
        'duration_nights',
        'group_size',
        'seats_available',
        'seats_left',
        'price',
        'price_note',
        'price_note_ur',
        'start_date',
        'end_date',
        'cover_image',
        'gallery_images',
        'short_description',
        'short_description_ur',
        'description',
        'description_ur',
        'highlights',
        'highlights_ur',
        'inclusions',
        'inclusions_ur',
        'exclusions',
        'exclusions_ur',
        'meta_title',
        'meta_title_ur',
        'meta_description',
        'meta_description_ur',
        'status',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'duration_days' => 'integer',
        'duration_nights' => 'integer',
        'group_size' => 'integer',
        'seats_available' => 'integer',
        'seats_left' => 'integer',
        'price' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'gallery_images' => 'array',
        'highlights' => 'array',
        'highlights_ur' => 'array',
        'inclusions' => 'array',
        'inclusions_ur' => 'array',
        'exclusions' => 'array',
        'exclusions_ur' => 'array',
        'is_featured' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (PakistanTour $tour): void {
            if (blank($tour->tour_no)) {
                $nextNumber = ((int) self::query()->max('id')) + 1;

                $tour->tour_no = 'PT-'.str_pad(
                    (string) $nextNumber,
                    5,
                    '0',
                    STR_PAD_LEFT
                );
            }

            if (blank($tour->slug)) {
                $baseSlug = Str::slug(
                    $tour->title.'-'.$tour->tour_no
                );

                $slug = $baseSlug;
                $counter = 2;

                while (
                    self::query()
                        ->where('slug', $slug)
                        ->when(
                            $tour->exists,
                            fn ($query) => $query->where(
                                $tour->getKeyName(),
                                '!=',
                                $tour->getKey()
                            )
                        )
                        ->exists()
                ) {
                    $slug = $baseSlug.'-'.$counter;
                    $counter++;
                }

                $tour->slug = $slug;
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getLocalizedTitleAttribute(): string
    {
        return app()->isLocale('ur') && filled($this->title_ur)
            ? $this->title_ur
            : $this->title;
    }

    public function getLocalizedSubtitleAttribute(): ?string
    {
        return app()->isLocale('ur') && filled($this->subtitle_ur)
            ? $this->subtitle_ur
            : $this->subtitle;
    }

    public function getLocalizedShortDescriptionAttribute(): ?string
    {
        return app()->isLocale('ur')
            && filled($this->short_description_ur)
                ? $this->short_description_ur
                : $this->short_description;
    }

    public function getLocalizedDescriptionAttribute(): ?string
    {
        return app()->isLocale('ur') && filled($this->description_ur)
            ? $this->description_ur
            : $this->description;
    }

    public function getLocalizedHighlightsAttribute(): array
    {
        return app()->isLocale('ur') && filled($this->highlights_ur)
            ? $this->highlights_ur
            : ($this->highlights ?? []);
    }

    public function getLocalizedInclusionsAttribute(): array
    {
        return app()->isLocale('ur') && filled($this->inclusions_ur)
            ? $this->inclusions_ur
            : ($this->inclusions ?? []);
    }

    public function getLocalizedExclusionsAttribute(): array
    {
        return app()->isLocale('ur') && filled($this->exclusions_ur)
            ? $this->exclusions_ur
            : ($this->exclusions ?? []);
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'PKR '.number_format((float) $this->price, 0);
    }

    public function getDurationTextAttribute(): string
    {
        if ($this->duration_days) {
            return $this->duration_days.' Day(s)';
        }

        return '-';
    }
}