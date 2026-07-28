<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BlogPost;
use App\Models\Promotion;
use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

final class HomepageContentService
{
    public function content(): array
    {
        return [
            'homepageTestimonials' => $this->testimonials(),
            'homepageNews' => $this->news(),
            'homepagePromotions' => $this->promotions(),
        ];
    }

    private function testimonials(): Collection
    {
        if (! $this->modelTableAvailable(Testimonial::class)) {
            return collect();
        }

        $model = new Testimonial;
        $query = Testimonial::query();

        $this->applyPublicStatus($query, $model->getTable());
        $this->applyPublishedDate($query, $model->getTable());
        $this->applyOrdering($query, $model->getTable());

        return $query
            ->limit(3)
            ->get()
            ->map(fn (Testimonial $testimonial): array => [
                'name' => $this->localized(
                    $testimonial,
                    ['name', 'customer_name', 'client_name'],
                    'Valued Customer',
                ),
                'role' => $this->localized(
                    $testimonial,
                    ['designation', 'role', 'service_type', 'location'],
                    'Travel Customer',
                ),
                'content' => $this->summary(
                    $this->localized(
                        $testimonial,
                        ['testimonial', 'content', 'review', 'message', 'description'],
                        '',
                    ),
                    220,
                ),
                'rating' => max(
                    1,
                    min(
                        5,
                        (int) ($this->attribute(
                            $testimonial,
                            ['rating', 'stars'],
                        ) ?: 5),
                    ),
                ),
            ])
            ->filter(
                static fn (array $item): bool => $item['content'] !== '',
            )
            ->values();
    }

    private function news(): Collection
    {
        if (! $this->modelTableAvailable(BlogPost::class)) {
            return collect();
        }

        $model = new BlogPost;
        $query = BlogPost::query();

        $this->applyPublicStatus($query, $model->getTable());
        $this->applyPublishedDate($query, $model->getTable());
        $this->applyOrdering($query, $model->getTable());

        return $query
            ->limit(3)
            ->get()
            ->map(fn (BlogPost $post): array => [
                'title' => $this->localized(
                    $post,
                    ['title', 'name'],
                    'Latest travel update',
                ),
                'excerpt' => $this->summary(
                    $this->localized(
                        $post,
                        ['excerpt', 'summary', 'short_description', 'content', 'description'],
                        '',
                    ),
                    170,
                ),
                'category' => $this->localized(
                    $post,
                    ['category', 'type'],
                    'Travel News',
                ),
                'date' => $this->displayDate($post),
                'url' => $this->modelRoute(
                    'website.blog.show',
                    $post,
                    'website.blog',
                ),
            ])
            ->values();
    }

    private function promotions(): Collection
    {
        if (! $this->modelTableAvailable(Promotion::class)) {
            return collect();
        }

        $model = new Promotion;
        $table = $model->getTable();
        $query = Promotion::query();

        $this->applyPublicStatus($query, $table);

        if (Schema::hasColumn($table, 'starts_at')) {
            $query->where(
                static fn (Builder $builder): Builder => $builder
                    ->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now()),
            );
        }

        if (Schema::hasColumn($table, 'ends_at')) {
            $query->where(
                static fn (Builder $builder): Builder => $builder
                    ->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now()),
            );
        }

        $this->applyOrdering($query, $table);

        return $query
            ->limit(3)
            ->get()
            ->map(fn (Promotion $promotion): array => [
                'title' => $this->localized(
                    $promotion,
                    ['title', 'name'],
                    'Current travel promotion',
                ),
                'excerpt' => $this->summary(
                    $this->localized(
                        $promotion,
                        ['short_description', 'excerpt', 'description', 'content'],
                        '',
                    ),
                    170,
                ),
                'type' => $this->localized(
                    $promotion,
                    ['promotion_type', 'type', 'category'],
                    'Special Offer',
                ),
                'code' => (string) (
                    $this->attribute(
                        $promotion,
                        ['promo_code', 'code', 'coupon_code'],
                    )
                    ?: ''
                ),
                'url' => $this->modelRoute(
                    'website.promotions.show',
                    $promotion,
                    'website.promotions',
                ),
            ])
            ->values();
    }

    private function applyPublicStatus(
        Builder $query,
        string $table,
    ): void {
        if (Schema::hasColumn($table, 'is_published')) {
            $query->where('is_published', true);
        }

        if (Schema::hasColumn($table, 'is_active')) {
            $query->where('is_active', true);
        }

        if (Schema::hasColumn($table, 'status')) {
            $query->whereIn(
                'status',
                [
                    'Published',
                    'published',
                    'Active',
                    'active',
                    'Approved',
                    'approved',
                ],
            );
        }
    }

    private function applyPublishedDate(
        Builder $query,
        string $table,
    ): void {
        if (! Schema::hasColumn($table, 'published_at')) {
            return;
        }

        $query->where(
            static fn (Builder $builder): Builder => $builder
                ->whereNull('published_at')
                ->orWhere('published_at', '<=', now()),
        );
    }

    private function applyOrdering(
        Builder $query,
        string $table,
    ): void {
        if (Schema::hasColumn($table, 'is_featured')) {
            $query->orderByDesc('is_featured');
        }

        foreach (
            [
                'published_at',
                'starts_at',
                'created_at',
                'id',
            ] as $column
        ) {
            if (Schema::hasColumn($table, $column)) {
                $query->orderByDesc($column);

                return;
            }
        }
    }

    private function localized(
        Model $model,
        array $baseFields,
        string $fallback,
    ): string {
        $fields = [];

        foreach ($baseFields as $field) {
            if (app()->isLocale('ur')) {
                $fields[] = $field.'_ur';
                $fields[] = $field.'_urdu';
            }

            $fields[] = $field;

            if (! app()->isLocale('ur')) {
                $fields[] = $field.'_en';
                $fields[] = $field.'_english';
            }
        }

        $value = $this->attribute($model, $fields);

        return is_scalar($value)
            ? trim((string) $value)
            : $fallback;
    }

    private function attribute(
        Model $model,
        array $fields,
    ): mixed {
        foreach ($fields as $field) {
            $value = $model->getAttribute($field);

            if (
                $value !== null
                && (! is_string($value) || trim($value) !== '')
            ) {
                return $value;
            }
        }

        return null;
    }

    private function displayDate(Model $model): string
    {
        foreach (
            [
                'published_at',
                'created_at',
                'updated_at',
            ] as $field
        ) {
            $value = $model->getAttribute($field);

            if ($value instanceof \DateTimeInterface) {
                return $value->format('d M Y');
            }

            if (is_string($value) && $value !== '') {
                try {
                    return Carbon::parse(
                        $value,
                    )->format('d M Y');
                } catch (Throwable) {
                    continue;
                }
            }
        }

        return '';
    }

    private function modelRoute(
        string $detailRoute,
        Model $model,
        string $fallbackRoute,
    ): string {
        try {
            if (Route::has($detailRoute)) {
                return route($detailRoute, $model);
            }
        } catch (Throwable) {
            // Use the safe index route below.
        }

        return Route::has($fallbackRoute)
            ? route($fallbackRoute)
            : url('/');
    }

    private function modelTableAvailable(
        string $modelClass,
    ): bool {
        $model = new $modelClass;

        return Schema::hasTable($model->getTable());
    }

    private function summary(
        string $value,
        int $limit,
    ): string {
        return Str::limit(
            trim(strip_tags($value)),
            $limit,
        );
    }
}
