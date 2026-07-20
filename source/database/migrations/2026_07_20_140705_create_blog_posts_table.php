<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'blog_posts',
            function (Blueprint $table): void {
                $table->id();

                $table->string('title');
                $table->string('title_ur')->nullable();

                $table->string('slug')
                    ->unique();

                $table->text('excerpt')->nullable();
                $table->text('excerpt_ur')->nullable();

                $table->longText('content');
                $table->longText('content_ur')->nullable();

                $table->string('category')
                    ->nullable()
                    ->index();

                $table->string('category_ur')->nullable();

                $table->string('featured_image_path')
                    ->nullable();

                $table->string('image_alt_text')
                    ->nullable();

                $table->string('image_alt_text_ur')
                    ->nullable();

                $table->string('author_name')
                    ->nullable();

                $table->json('tags')
                    ->nullable();

                $table->string('seo_title')
                    ->nullable();

                $table->string('seo_title_ur')
                    ->nullable();

                $table->text('seo_description')
                    ->nullable();

                $table->text('seo_description_ur')
                    ->nullable();

                $table->string('status')
                    ->default('Draft')
                    ->index();

                $table->boolean('is_featured')
                    ->default(false)
                    ->index();

                $table->unsignedInteger('sort_order')
                    ->default(0);

                $table->unsignedBigInteger('view_count')
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
            },
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};