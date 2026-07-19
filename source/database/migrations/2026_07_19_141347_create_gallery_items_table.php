<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_items', function (Blueprint $table): void {
            $table->id();

            $table->string('title');
            $table->string('title_ur')->nullable();
            $table->string('slug')->unique();

            $table->string('category')->nullable()->index();
            $table->string('category_ur')->nullable();

            $table->string('album')->nullable();
            $table->string('album_ur')->nullable();

            $table->string('media_type')->default('Image');
            $table->string('image_path')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->string('video_url')->nullable();

            $table->string('alt_text')->nullable();
            $table->string('alt_text_ur')->nullable();

            $table->text('caption')->nullable();
            $table->text('caption_ur')->nullable();

            $table->string('location')->nullable();
            $table->string('location_ur')->nullable();
            $table->date('event_date')->nullable();

            $table->string('photographer')->nullable();

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
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_items');
    }
};