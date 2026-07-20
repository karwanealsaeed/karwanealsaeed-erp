<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $fillable = [
        'question',
        'question_ur',
        'answer',
        'answer_ur',
        'category',
        'category_ur',
        'status',
        'is_featured',
        'sort_order',
        'published_at',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (Faq $faq): void {
            if (
                $faq->status === 'Published'
                && blank($faq->published_at)
            ) {
                $faq->published_at = now();
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

    public function getLocalizedQuestionAttribute(): string
    {
        return app()->isLocale('ur')
            && filled($this->question_ur)
                ? $this->question_ur
                : $this->question;
    }

    public function getLocalizedAnswerAttribute(): string
    {
        return app()->isLocale('ur')
            && filled($this->answer_ur)
                ? $this->answer_ur
                : $this->answer;
    }

    public function getLocalizedCategoryAttribute(): ?string
    {
        return app()->isLocale('ur')
            && filled($this->category_ur)
                ? $this->category_ur
                : $this->category;
    }
}