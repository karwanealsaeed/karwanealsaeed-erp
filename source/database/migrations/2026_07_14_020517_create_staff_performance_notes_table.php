<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_performance_notes', function (Blueprint $table): void {
            $table->id();

            $table->string('note_no', 50)
                ->nullable()
                ->unique();

            $table->foreignId('staff_id')
                ->constrained('staff')
                ->cascadeOnDelete();

            $table->date('review_date');
            $table->date('next_review_date')->nullable();

            $table->string('review_type', 50)
                ->default('Performance Review');

            $table->string('title');

            $table->decimal('rating', 3, 1)->nullable();

            $table->text('strengths')->nullable();
            $table->text('improvements')->nullable();
            $table->text('goals')->nullable();
            $table->text('notes')->nullable();

            $table->string('attachment')->nullable();

            $table->string('status', 30)
                ->default('Draft')
                ->index();

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(
                ['staff_id', 'review_date'],
                'staff_performance_review_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_performance_notes');
    }
};