<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'testimonials',
            function (Blueprint $table): void {
                $table->id();

                $table->string('customer_name');
                $table->string('customer_name_ur')->nullable();

                $table->string('designation')->nullable();
                $table->string('designation_ur')->nullable();
                $table->string('company_name')->nullable();

                $table->string('location')->nullable();
                $table->string('location_ur')->nullable();
                $table->string('country')->nullable();

                $table->string('photo_path')->nullable();

                $table->unsignedTinyInteger('rating')
                    ->default(5);

                $table->text('testimonial');
                $table->text('testimonial_ur')->nullable();

                $table->string('service_type')->nullable()
                    ->index();

                $table->date('travel_date')->nullable();

                $table->string('video_url')->nullable();

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
            },
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};