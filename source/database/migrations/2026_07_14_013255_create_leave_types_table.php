<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_types', function (Blueprint $table): void {
            $table->id();

            $table->string('name')->unique();
            $table->string('code', 30)->nullable()->unique();

            /*
             * 0 quota ka matlab unlimited / manually controlled.
             */
            $table->decimal('annual_quota', 8, 2)->default(0);

            $table->boolean('is_paid')->default(true);
            $table->unsignedInteger('attachment_required_after_days')
                ->default(0);

            $table->string('status', 20)
                ->default('Active')
                ->index();

            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};