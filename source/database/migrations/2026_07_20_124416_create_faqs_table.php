<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'faqs',
            function (Blueprint $table): void {
                $table->id();

                $table->string('question');
                $table->string('question_ur')->nullable();

                $table->text('answer');
                $table->text('answer_ur')->nullable();

                $table->string('category')
                    ->nullable()
                    ->index();

                $table->string('category_ur')
                    ->nullable();

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
        Schema::dropIfExists('faqs');
    }
};