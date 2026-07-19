<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pakistan_tours', function (Blueprint $table) {
            $table->id();

            $table->string('tour_no')->unique();
            $table->string('title');
            $table->string('title_ur')->nullable();
            $table->string('slug')->unique()->nullable();

            $table->string('subtitle')->nullable();
            $table->string('subtitle_ur')->nullable();

            $table->string('destination')->index();
            $table->string('province')->nullable();
            $table->string('departure_city')->nullable();

            $table->unsignedSmallInteger('duration_days')->nullable();
            $table->unsignedSmallInteger('duration_nights')->nullable();
            $table->unsignedInteger('group_size')->nullable();
            $table->unsignedInteger('seats_available')->nullable();
            $table->unsignedInteger('seats_left')->nullable();

            $table->decimal('price', 15, 2)->default(0);
            $table->string('price_note')->nullable();
            $table->string('price_note_ur')->nullable();

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->string('cover_image')->nullable();
            $table->json('gallery_images')->nullable();

            $table->text('short_description')->nullable();
            $table->text('short_description_ur')->nullable();
            $table->longText('description')->nullable();
            $table->longText('description_ur')->nullable();

            $table->json('highlights')->nullable();
            $table->json('highlights_ur')->nullable();
            $table->json('inclusions')->nullable();
            $table->json('inclusions_ur')->nullable();
            $table->json('exclusions')->nullable();
            $table->json('exclusions_ur')->nullable();

            $table->string('meta_title')->nullable();
            $table->string('meta_title_ur')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_description_ur')->nullable();

            $table->string('status')
                ->default('Draft')
                ->index();

            $table->boolean('is_featured')
                ->default(false)
                ->index();

            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pakistan_tours');
    }
};