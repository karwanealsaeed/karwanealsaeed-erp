<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'promotions',
            function (Blueprint $table): void {
                $table->id();

                $table->string('title');
                $table->string('title_ur')->nullable();

                $table->string('slug')->unique();

                $table->string('subtitle')->nullable();
                $table->string('subtitle_ur')->nullable();

                $table->text('description')->nullable();
                $table->text('description_ur')->nullable();

                $table->string('promotion_type')
                    ->nullable()
                    ->index();

                $table->string('promo_code')
                    ->nullable()
                    ->index();

                $table->string('discount_type')->nullable();

                $table->decimal(
                    'discount_value',
                    12,
                    2,
                )->nullable();

                $table->string('currency', 3)
                    ->nullable();

                $table->decimal(
                    'original_price',
                    12,
                    2,
                )->nullable();

                $table->decimal(
                    'offer_price',
                    12,
                    2,
                )->nullable();

                $table->string(
                    'featured_image_path',
                )->nullable();

                $table->string('badge_text')->nullable();
                $table->string('badge_text_ur')->nullable();

                $table->string('cta_text')->nullable();
                $table->string('cta_text_ur')->nullable();

                $table->string('target_url')->nullable();

                $table->text('terms')->nullable();
                $table->text('terms_ur')->nullable();

                $table->timestamp('starts_at')
                    ->nullable()
                    ->index();

                $table->timestamp('ends_at')
                    ->nullable()
                    ->index();

                $table->string('status')
                    ->default('Draft')
                    ->index();

                $table->boolean('is_featured')
                    ->default(false)
                    ->index();

                $table->unsignedInteger('sort_order')
                    ->default(0);

                $table->timestamp('published_at')
                    ->nullable()
                    ->index();

                $table->timestamps();

                $table->index([
                    'status',
                    'is_featured',
                    'sort_order',
                ]);

                $table->index([
                    'starts_at',
                    'ends_at',
                ]);
            },
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};